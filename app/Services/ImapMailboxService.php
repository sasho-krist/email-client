<?php

namespace App\Services;

use App\Models\EmailAccount;
use Carbon\Carbon;
use Webklex\PHPIMAP\Client;
use Webklex\PHPIMAP\ClientManager;
use Webklex\PHPIMAP\Folder;
use Webklex\PHPIMAP\Message;
use Webklex\PHPIMAP\Support\FolderCollection;
use Webklex\PHPIMAP\Support\MessageCollection;

class ImapMailboxService
{
    public function __construct(
        protected ClientManager $clientManager
    ) {}

    public function makeClient(EmailAccount $account): Client
    {
        $config = [
            'host' => $account->imap_host,
            'port' => $account->imap_port,
            'encryption' => $this->mapImapEncryption($account->imap_security),
            'validate_cert' => true,
            'username' => $account->email,
            'password' => $account->mailbox_password,
            'protocol' => 'imap',
            'authentication' => $account->imap_auth === 'oauth' ? 'oauth' : null,
            'timeout' => 35,
        ];

        $client = $this->clientManager->make($config);
        $client->connect();

        return $client;
    }

    /**
     * @throws \Throwable
     */
    public function testImap(EmailAccount $account): void
    {
        $client = $this->makeClient($account);
        $client->disconnect();
    }

    /**
     * Намиране на системни папки при първа връзка.
     */
    public function detectSpecialFolders(EmailAccount $account): void
    {
        if ($account->folder_sent && $account->folder_spam && $account->folder_trash) {
            return;
        }

        $client = $this->makeClient($account);
        try {
            $paths = $this->listAllImapFolderPaths($client);
            $lower = array_map(static fn ($p) => mb_strtolower($p), $paths);

            $sent = $this->pickFolder($paths, $lower, $this->needlesForRole('sent'));
            $spam = $this->pickFolder($paths, $lower, $this->needlesForRole('spam'));
            $trash = $this->pickFolder($paths, $lower, $this->needlesForRole('trash'));

            $account->update(array_filter([
                'folder_sent' => $sent ?? $account->folder_sent,
                'folder_spam' => $spam ?? $account->folder_spam,
                'folder_trash' => $trash ?? $account->folder_trash,
            ], static fn ($v) => $v !== null));
        } finally {
            $client->disconnect();
        }
    }

    /**
     * Изчиства записаните системни папки и отново ги открива през IMAP LIST (полезно след промяна в Gmail „Етикети“).
     */
    public function rediscoverSpecialFolders(EmailAccount $account): void
    {
        $account->forceFill([
            'folder_sent' => null,
            'folder_spam' => null,
            'folder_trash' => null,
        ])->saveQuietly();

        $this->detectSpecialFolders($account->fresh());
    }

    /**
     * Пълен списък от пътища (вкл. вложени под [Gmail]), нужен за Gmail и други държатели на дърво.
     */
    protected function flattenFolderPaths(FolderCollection $folders): array
    {
        $paths = [];
        foreach ($folders as $folder) {
            $paths[] = $folder->path;
            if ($folder->children->count() > 0) {
                $paths = array_merge($paths, $this->flattenFolderPaths($folder->children));
            }
        }

        return $paths;
    }

    /**
     * Пълно обединение от LIST "" "*" (плоско) и йерархично дърво — Gmail понякога дава всички кутии само в плоския списък.
     *
     * @return array<int, string>
     */
    protected function listAllImapFolderPaths(Client $client): array
    {
        $paths = [];
        foreach ($client->getFolders(false, null, true) as $folder) {
            $paths[] = $folder->path;
        }
        foreach ($this->flattenFolderPaths($client->getFolders(true, null, true)) as $p) {
            $paths[] = $p;
        }

        return array_values(array_unique($paths));
    }

    protected function isGoogleImapHost(string $host): bool
    {
        $h = strtolower($host);

        return str_contains($h, 'gmail')
            || str_contains($h, 'googlemail');
    }

    /**
     * Подстрингове за откриване на системни папки (малки букви); по-специфичните са първи.
     *
     * @return array<int, string>
     */
    protected function needlesForRole(string $role): array
    {
        return match ($role) {
            'sent' => [
                '[gmail]/sent mail',
                '[gmail]/изпратени',
                '[google mail]/sent mail',
                '[google mail]/изпратени',
                '[gmail]/sent',
                '[google mail]/sent',
                'inbox/sent',
                'sent items',
                '/sent',
                'изпратени',
                'gesendet',
            ],
            'spam' => [
                '[gmail]/spam',
                '[gmail]/спам',
                '[google mail]/spam',
                '[google mail]/спам',
                'junk e-mail',
                '/junk',
                'нежелана',
                'junk',
                'spam',
            ],
            'trash' => [
                '[gmail]/trash',
                '[gmail]/кошче',
                '[google mail]/trash',
                '[google mail]/кошче',
                '[gmail]/bin',
                'deleted items',
                'кошче',
                'trash',
                'deleted',
                'bin',
            ],
            default => [],
        };
    }

    /**
     * @param  array<int, string>  $paths
     * @param  array<int, string>  $lower
     */
    protected function pickFolder(array $paths, array $lower, array $needles): ?string
    {
        foreach ($needles as $n) {
            foreach ($lower as $i => $p) {
                if (str_contains($p, $n)) {
                    return $paths[$i];
                }
            }
        }

        return null;
    }

    /**
     * Опит за отваряне по път с различни режими на кодиране (UTF-8 / UTF-7-IMAP).
     */
    protected function tryOpenFolderByPath(Client $client, string $path): ?Folder
    {
        $folder = $client->getFolderByPath($path, false, true)
            ?? $client->getFolderByPath($path, true, true)
            ?? $client->getFolder($path, null, false)
            ?? $client->getFolder($path, null, true);

        if ($folder instanceof Folder) {
            return $folder;
        }

        return $this->findFolderByPathCaseInsensitive($client, $path);
    }

    protected function findFolderByPathCaseInsensitive(Client $client, string $path): ?Folder
    {
        $want = mb_strtolower($path);
        foreach ($client->getFolders(false, null, true) as $folder) {
            if (mb_strtolower($folder->path) === $want) {
                return $folder;
            }
        }
        foreach ($this->walkFoldersDepthFirst($client->getFolders(true, null, true)) as $folder) {
            if (mb_strtolower($folder->path) === $want) {
                return $folder;
            }
        }

        return null;
    }

    /**
     * @return \Generator<int, Folder>
     */
    protected function walkFoldersDepthFirst(FolderCollection $folders): \Generator
    {
        foreach ($folders as $folder) {
            yield $folder;
            if ($folder->children->count() > 0) {
                yield from $this->walkFoldersDepthFirst($folder->children);
            }
        }
    }

    /**
     * Намира реалния път на сървъра при разминаване със записания в БД (често при Gmail).
     */
    protected function discoverPathForRole(Client $client, string $role): ?string
    {
        $paths = $this->listAllImapFolderPaths($client);
        $lower = array_map(static fn ($p) => mb_strtolower($p), $paths);

        return $this->pickFolder($paths, $lower, $this->needlesForRole($role));
    }

    protected function persistFolderPathIfNeeded(EmailAccount $account, string $role, string $resolvedPath): void
    {
        $field = match ($role) {
            'sent' => 'folder_sent',
            'spam' => 'folder_spam',
            'trash' => 'folder_trash',
            default => null,
        };
        if ($field === null) {
            return;
        }

        if ($account->getAttribute($field) !== $resolvedPath) {
            $account->forceFill([$field => $resolvedPath])->saveQuietly();
        }
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function listMessages(EmailAccount $account, string $folderRole, int $limit = 50): array
    {
        $client = $this->makeClient($account);
        try {
            $folder = $this->openFolderByRole($client, $account, $folderRole);
            $query = $folder->messages()->all()->setFetchOrderDesc()->limit($limit, 1);
            $query->leaveUnread();

            /** @var MessageCollection $messages */
            $messages = $query->get();

            $rows = [];
            foreach ($messages as $message) {
                $rows[] = $this->serializeListRow($message);
            }

            return $rows;
        } finally {
            $client->disconnect();
        }
    }

    /**
     * @return array<string, mixed>
     */
    public function getMessage(EmailAccount $account, string $folderRole, int $uid): array
    {
        $client = $this->makeClient($account);
        try {
            $folder = $this->openFolderByRole($client, $account, $folderRole);
            $query = $folder->messages()->whereUid($uid);

            /** @var MessageCollection $messages */
            $messages = $query->get();
            $message = $messages->first();
            if (! $message instanceof Message) {
                throw new \RuntimeException('Съобщението не е намерено.');
            }

            $message->setFlag('Seen');

            return $this->serializeFullMessage($message);
        } finally {
            $client->disconnect();
        }
    }

    public function deleteMessage(EmailAccount $account, string $folderRole, int $uid): void
    {
        $client = $this->makeClient($account);
        try {
            $folder = $this->openFolderByRole($client, $account, $folderRole);
            $query = $folder->messages()->whereUid($uid);
            /** @var MessageCollection $messages */
            $messages = $query->get();
            $message = $messages->first();
            if (! $message instanceof Message) {
                throw new \RuntimeException('Съобщението не е намерено.');
            }

            match ($account->delete_behavior) {
                'move_trash' => $message->move($account->folder_trash ?: 'Trash', false),
                'mark_deleted' => $message->setFlag('Deleted'),
                default => $message->delete(true),
            };
        } finally {
            $client->disconnect();
        }
    }

    protected function openFolderByRole(Client $client, EmailAccount $account, string $role): Folder
    {
        $path = match ($role) {
            'inbox' => $account->folder_inbox ?: 'INBOX',
            'sent' => $account->folder_sent ?: $this->fallbackSent($account),
            'spam' => $account->folder_spam ?: $this->fallbackSpam($account),
            'trash' => $account->folder_trash ?: $this->fallbackTrash($account),
            default => 'INBOX',
        };

        $folder = $this->tryOpenFolderByPath($client, $path);

        if (! $folder instanceof Folder && in_array($role, ['sent', 'spam', 'trash'], true)) {
            foreach ($this->alternateFallbackPaths($account, $role) as $alt) {
                if ($alt === '' || $alt === $path) {
                    continue;
                }
                $folder = $this->tryOpenFolderByPath($client, $alt);
                if ($folder instanceof Folder) {
                    $this->persistFolderPathIfNeeded($account, $role, $alt);
                    break;
                }
            }
        }

        if (! $folder instanceof Folder && in_array($role, ['sent', 'spam', 'trash'], true)) {
            $discovered = $this->discoverPathForRole($client, $role);
            if ($discovered !== null) {
                $this->persistFolderPathIfNeeded($account, $role, $discovered);
                $folder = $this->tryOpenFolderByPath($client, $discovered);
            }
        }

        if (! $folder instanceof Folder) {
            $hint = $this->isGoogleImapHost($account->imap_host)
                ? ' Ако е Gmail: използвайте имейл @gmail.com или `imap.gmail.com` / `imap.googlemail.com`; натиснете „Преоткрий системните папки“ в настройките.'
                : '';

            throw new \RuntimeException('Папката не е намерена на сървъра: '.$path.'.'.$hint);
        }

        return $folder;
    }

    /**
     * Допълнителни типични пътища при Gmail / Google Workspace.
     *
     * @return array<int, string>
     */
    protected function alternateFallbackPaths(EmailAccount $account, string $role): array
    {
        if (! $this->isGoogleImapHost($account->imap_host)) {
            return [];
        }

        return match ($role) {
            'sent' => [
                '[Google Mail]/Sent Mail',
                '[Gmail]/Sent',
                '[Google Mail]/Sent',
                '[Gmail]/Изпратени',
                '[Google Mail]/Изпратени',
            ],
            'spam' => [
                '[Google Mail]/Spam',
                '[Gmail]/Bulk Mail',
                '[Gmail]/Спам',
                '[Google Mail]/Спам',
            ],
            'trash' => [
                '[Google Mail]/Trash',
                '[Gmail]/Bin',
                '[Gmail]/Кошче',
                '[Google Mail]/Кошче',
            ],
            default => [],
        };
    }

    protected function fallbackSent(EmailAccount $account): string
    {
        return $this->isGoogleImapHost($account->imap_host)
            ? '[Gmail]/Sent Mail'
            : 'Sent';
    }

    protected function fallbackSpam(EmailAccount $account): string
    {
        return $this->isGoogleImapHost($account->imap_host)
            ? '[Gmail]/Spam'
            : 'Spam';
    }

    protected function fallbackTrash(EmailAccount $account): string
    {
        return $this->isGoogleImapHost($account->imap_host)
            ? '[Gmail]/Trash'
            : 'Trash';
    }

    protected function mapImapEncryption(string $sec): bool|string
    {
        return match ($sec) {
            'ssl', 'tls' => 'ssl',
            'starttls' => 'starttls',
            default => false,
        };
    }

    /**
     * @return array<string, mixed>
     */
    protected function serializeListRow(Message $message): array
    {
        $from = $message->getFrom();
        $first = null;
        if ($from !== null && method_exists($from, 'first')) {
            $first = $from->first();
        }
        $fromStr = $first ? trim(($first->personal ? $first->personal.' ' : '').'<'.($first->mail ?: $first->full).'>') : '';

        $date = $message->getDate();
        $carbon = $date ? Carbon::parse((string) $date) : null;

        $seen = $message->hasFlag('Seen');

        $preview = '';
        try {
            $preview = mb_substr(strip_tags((string) $message->getTextBody()), 0, 140);
        } catch (\Throwable) {
            $preview = '';
        }

        return [
            'uid' => $message->getUid(),
            'subject' => (string) $message->getSubject(),
            'from' => trim($fromStr),
            'date' => $carbon,
            'seen' => $seen,
            'preview' => $preview,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function serializeFullMessage(Message $message): array
    {
        $html = $message->getHTMLBody();
        $text = $message->getTextBody();

        return [
            'uid' => $message->getUid(),
            'subject' => (string) $message->getSubject(),
            'from' => (string) $message->getFrom(),
            'to' => (string) $message->getTo(),
            'date' => $message->getDate() ? Carbon::parse((string) $message->getDate()) : null,
            'body_html' => $html ?: null,
            'body_text' => $text ? (string) $text : null,
        ];
    }
}
