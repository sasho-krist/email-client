<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\Concerns\ApiResponses;
use App\Http\Controllers\Controller;
use App\Models\EmailAccount;
use App\Services\ImapMailboxService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MailboxApiController extends Controller
{
    use ApiResponses;

    public function index(Request $request, EmailAccount $account, string $folder, ImapMailboxService $mailbox): JsonResponse
    {
        $this->authorize('view', $account);

        $folderKey = $this->normalizeFolder($folder);

        try {
            $messages = $mailbox->listMessages($account, $folderKey);
            $payload = array_map(fn (array $row) => $this->serializeMessageRow($row), $messages);

            return $this->ok([
                'folder' => $folderKey,
                'messages' => $payload,
            ]);
        } catch (\Throwable $e) {
            report($e);

            return $this->fail('Неуспешно зареждане на съобщенията: '.$e->getMessage(), 502);
        }
    }

    public function show(Request $request, EmailAccount $account, string $folder, int $uid, ImapMailboxService $mailbox): JsonResponse
    {
        $this->authorize('view', $account);

        $folderKey = $this->normalizeFolder($folder);

        try {
            $message = $mailbox->getMessage($account, $folderKey, $uid);

            return $this->ok(['message' => $this->serializeFullMessage($message)]);
        } catch (\Throwable $e) {
            report($e);

            return $this->fail('Съобщението не е намерено или не може да се прочете.', 404);
        }
    }

    public function destroy(Request $request, EmailAccount $account, string $folder, int $uid, ImapMailboxService $mailbox): JsonResponse
    {
        $this->authorize('view', $account);

        $folderKey = $this->normalizeFolder($folder);

        try {
            $mailbox->deleteMessage($account, $folderKey, $uid);

            return $this->ok(['message' => 'Съобщението е обработено.']);
        } catch (\Throwable $e) {
            report($e);

            return $this->fail('Неуспешно изтриване на съобщението: '.$e->getMessage(), 502);
        }
    }

    protected function normalizeFolder(string $folder): string
    {
        return strtolower($folder);
    }

    /**
     * @param  array<string, mixed>  $row
     * @return array<string, mixed>
     */
    protected function serializeMessageRow(array $row): array
    {
        $date = $row['date'] ?? null;

        return [
            'uid' => $row['uid'],
            'subject' => $row['subject'],
            'from' => $row['from'],
            'date' => $date ? $date->toIso8601String() : null,
            'seen' => $row['seen'],
            'preview' => $row['preview'],
        ];
    }

    /**
     * @param  array<string, mixed>  $message
     * @return array<string, mixed>
     */
    protected function serializeFullMessage(array $message): array
    {
        $date = $message['date'] ?? null;

        return [
            'uid' => $message['uid'],
            'subject' => $message['subject'],
            'from' => $message['from'],
            'to' => $message['to'],
            'date' => $date instanceof Carbon ? $date->toIso8601String() : null,
            'body_html' => $message['body_html'],
            'body_text' => $message['body_text'],
        ];
    }
}
