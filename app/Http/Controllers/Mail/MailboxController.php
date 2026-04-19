<?php

namespace App\Http\Controllers\Mail;

use App\Http\Controllers\Controller;
use App\Models\EmailAccount;
use App\Services\ImapMailboxService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MailboxController extends Controller
{
    public function folder(Request $request, EmailAccount $account, string $folder): View
    {
        $this->authorize('view', $account);

        $folderKey = $this->normalizeFolder($folder);

        $pref = $request->user()->mailPreferenceOrCreate();
        $service = app(ImapMailboxService::class);

        try {
            $messages = $service->listMessages($account, $folderKey);
        } catch (\Throwable $e) {
            return view('mail.mailbox.folder', [
                'account' => $account,
                'folder' => $folderKey,
                'messages' => collect(),
                'grouped' => collect(),
                'error' => $e->getMessage(),
                'groupBy' => $pref->inbox_group_by,
            ]);
        }

        $collection = collect($messages);

        $grouped = collect();
        if ($folderKey === 'inbox' && $pref->inbox_group_by === 'date') {
            $grouped = $collection->groupBy(fn ($m) => optional($m['date'])->toDateString() ?? 'unknown');
        }

        return view('mail.mailbox.folder', [
            'account' => $account,
            'folder' => $folderKey,
            'messages' => $collection,
            'grouped' => $grouped,
            'error' => null,
            'groupBy' => $pref->inbox_group_by,
        ]);
    }

    public function message(Request $request, EmailAccount $account, string $folder, int $uid): View
    {
        $this->authorize('view', $account);

        $folderKey = $this->normalizeFolder($folder);
        $service = app(ImapMailboxService::class);

        try {
            $message = $service->getMessage($account, $folderKey, $uid);
        } catch (\Throwable $e) {
            abort(404, $e->getMessage());
        }

        return view('mail.mailbox.message', [
            'account' => $account,
            'folder' => $folderKey,
            'message' => $message,
        ]);
    }

    public function destroyMessage(Request $request, EmailAccount $account, string $folder, int $uid): RedirectResponse
    {
        $this->authorize('view', $account);

        $folderKey = $this->normalizeFolder($folder);

        try {
            app(ImapMailboxService::class)->deleteMessage($account, $folderKey, $uid);
        } catch (\Throwable $e) {
            return back()->withErrors(['mail' => $e->getMessage()]);
        }

        return redirect()
            ->route('mail.folder', [$account, $folderKey])
            ->with('status', 'Съобщението е обработено.');
    }

    protected function normalizeFolder(string $folder): string
    {
        $f = strtolower($folder);

        return match ($f) {
            'inbox', 'sent', 'spam', 'trash' => $f,
            default => abort(404),
        };
    }
}
