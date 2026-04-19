<?php

namespace App\Http\Controllers\Mail;

use App\Http\Controllers\Controller;
use App\Models\EmailAccount;
use App\Services\ImapMailboxService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class MailSettingsController extends Controller
{
    public function edit(Request $request): View
    {
        $account = $this->resolveAccount($request);
        $this->authorize('update', $account);

        $pref = $request->user()->mailPreferenceOrCreate();
        $tab = $request->query('tab', 'server');

        return view('mail.settings.edit', compact('account', 'pref', 'tab'));
    }

    public function rediscoverFolders(Request $request, ImapMailboxService $imap): RedirectResponse
    {
        $account = $this->resolveAccount($request);
        $this->authorize('update', $account);

        try {
            $imap->rediscoverSpecialFolders($account);
        } catch (\Throwable $e) {
            report($e);

            return back()->withErrors([
                'folders' => 'Неуспешно откриване на папки. Проверете паролата към пощата, че IMAP е включен в Gmail и че етикетите са видими за IMAP (виж съобщението за настройки на Gmail).',
            ]);
        }

        return back()->with(
            'status',
            'Системните папки бяха преоткрити. Отворете отново Изходящи, Спам или Кошче.'
        );
    }

    public function update(Request $request): RedirectResponse
    {
        $account = $this->resolveAccount($request);
        $this->authorize('update', $account);

        $tab = $request->input('tab', 'server');

        if (! in_array($tab, ['server', 'signature', 'profile', 'display', 'reply'], true)) {
            return back()->withErrors(['save' => 'Невалиден раздел за настройки.']);
        }

        try {
            if ($tab === 'server') {
                $data = $request->validate([
                    'imap_host' => ['required', 'string', 'max:255'],
                    'imap_port' => ['required', 'integer', 'min:1', 'max:65535'],
                    'imap_security' => ['required', 'in:ssl,starttls,none,tls'],
                    'imap_auth' => ['required', 'in:password,oauth'],
                    'smtp_host' => ['required', 'string', 'max:255'],
                    'smtp_port' => ['required', 'integer', 'min:1', 'max:65535'],
                    'smtp_security' => ['required', 'in:ssl,starttls,none,tls'],
                    'smtp_auth' => ['required', 'in:password,oauth'],
                    'check_on_startup' => ['sometimes', 'boolean'],
                    'check_interval_minutes' => ['nullable', 'integer', 'min:1', 'max:1440'],
                    'use_idle' => ['sometimes', 'boolean'],
                    'delete_behavior' => ['required', 'in:move_trash,mark_deleted,delete_immediate'],
                    'mailbox_password' => ['nullable', 'string'],
                ]);

                DB::transaction(function () use ($account, $data): void {
                    $account->fill(collect($data)->except(['mailbox_password'])->all());

                    if (! empty($data['mailbox_password'])) {
                        $account->mailbox_password = $data['mailbox_password'];
                    }

                    $account->save();
                });
            }

            if ($tab === 'signature') {
                $data = $request->validate([
                    'signature_html' => ['nullable', 'string'],
                    'signature_use_html' => ['sometimes', 'boolean'],
                ]);

                DB::transaction(function () use ($account, $data, $request): void {
                    $account->signature_html = $data['signature_html'] ?? null;
                    $account->signature_use_html = $request->boolean('signature_use_html', true);
                    $account->save();
                });
            }

            if ($tab === 'profile') {
                $data = $request->validate([
                    'profile_name' => ['nullable', 'string', 'max:255'],
                    'account_color' => ['nullable', 'regex:/^#[0-9a-fA-F]{6}$/'],
                    'display_name' => ['nullable', 'string', 'max:255'],
                    'reply_to' => ['nullable', 'email'],
                    'organization' => ['nullable', 'string', 'max:255'],
                ]);

                DB::transaction(function () use ($account, $data): void {
                    $account->update($data);
                });
            }

            if ($tab === 'display') {
                $validated = $request->validate([
                    'inbox_group_by' => ['required', 'in:none,date'],
                ]);

                $pref = $request->user()->mailPreferenceOrCreate();

                DB::transaction(function () use ($pref, $validated): void {
                    $pref->update($validated);
                });
            }

            if ($tab === 'reply') {
                $pref = $request->user()->mailPreferenceOrCreate();

                DB::transaction(function () use ($pref, $request): void {
                    $pref->update([
                        'reply_include_quote' => $request->boolean('reply_include_quote'),
                        'reply_top_posting' => $request->boolean('reply_top_posting'),
                    ]);
                });
            }
        } catch (ValidationException $e) {
            throw $e;
        } catch (\Throwable $e) {
            report($e);

            return back()
                ->withInput()
                ->withErrors(['save' => 'Неуспешно запазване на настройките. Опитайте отново.']);
        }

        return back()->with('status', 'Настройките са запазени.');
    }

    protected function resolveAccount(Request $request): EmailAccount
    {
        $id = $request->query('account') ?? $request->input('account');

        if ($id) {
            return EmailAccount::where('user_id', $request->user()->id)->findOrFail($id);
        }

        return EmailAccount::query()
            ->where('user_id', $request->user()->id)
            ->orderBy('id')
            ->firstOrFail();
    }
}
