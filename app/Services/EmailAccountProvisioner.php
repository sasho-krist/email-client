<?php

namespace App\Services;

use App\Models\EmailAccount;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class EmailAccountProvisioner
{
    public function __construct(
        protected MailDiscoveryService $discoveryService,
        protected ImapMailboxService $imapMailbox,
    ) {}

    /**
     * Връща масив входни настройки (incoming) след откриване или от ръчни полета.
     *
     * @return array{incoming: array<string, mixed>|null, error?: string}
     */
    public function resolveIncoming(Request $request, array $validated, bool $manual): array
    {
        if ($manual) {
            return ['incoming' => [
                'imap_host' => $validated['imap_host'],
                'imap_port' => (int) $validated['imap_port'],
                'imap_security' => $validated['imap_security'],
                'imap_auth' => $validated['imap_auth'],
                'smtp_host' => $validated['smtp_host'],
                'smtp_port' => (int) $validated['smtp_port'],
                'smtp_security' => $validated['smtp_security'],
                'smtp_auth' => $validated['smtp_auth'],
            ]];
        }

        $found = $this->discoveryService->discover($validated['email']);
        if ($found === null) {
            return ['incoming' => null, 'error' => 'Не бяха открити автоматични настройки за този доставчик. Моля, въведете ги ръчно.'];
        }

        return ['incoming' => $found];
    }

    /**
     * Създава акаунт с тест на IMAP и транзакция при запис в базата.
     *
     * @throws \Throwable
     */
    public function createAccount(User $user, array $validated, array $incoming, Request $request): EmailAccount
    {
        $account = new EmailAccount([
            'user_id' => $user->id,
            'profile_name' => $validated['profile_name'] ?? null,
            'email' => $validated['email'],
            'mailbox_password' => $validated['mailbox_password'],
            'display_name' => $validated['display_name'] ?? null,
            'reply_to' => $validated['reply_to'] ?? null,
            'organization' => $validated['organization'] ?? null,
            'imap_host' => $incoming['imap_host'],
            'imap_port' => (int) $incoming['imap_port'],
            'imap_security' => $incoming['imap_security'],
            'imap_auth' => 'password',
            'smtp_host' => $incoming['smtp_host'],
            'smtp_port' => (int) $incoming['smtp_port'],
            'smtp_security' => $incoming['smtp_security'],
            'smtp_auth' => 'password',
            'check_on_startup' => $request->boolean('check_on_startup', true),
            'check_interval_minutes' => (int) ($validated['check_interval_minutes'] ?? $request->input('check_interval_minutes', 10)),
            'use_idle' => $request->boolean('use_idle', true),
            'delete_behavior' => $validated['delete_behavior'] ?? $request->input('delete_behavior', 'move_trash'),
            'signature_html' => $validated['signature_html'] ?? $request->input('signature_html'),
            'signature_use_html' => $request->boolean('signature_use_html', true),
        ]);

        try {
            $this->imapMailbox->testImap($account);
        } catch (\Throwable $e) {
            throw new \RuntimeException('Неуспешна връзка към пощенския сървър: '.$e->getMessage(), 0, $e);
        }

        try {
            DB::transaction(function () use ($account): void {
                $account->save();
            });
        } catch (\Throwable $e) {
            throw new \RuntimeException('Грешка при запис на акаунта в базата данни.', 0, $e);
        }

        try {
            $this->imapMailbox->detectSpecialFolders($account);
        } catch (\Throwable) {
            // не е критично
        }

        return $account->fresh();
    }

    /**
     * Изтриване на акаунт с транзакция.
     *
     * @throws \Throwable
     */
    public function deleteAccount(EmailAccount $account): void
    {
        DB::transaction(function () use ($account): void {
            $account->delete();
        });
    }
}
