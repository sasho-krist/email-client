<?php

namespace App\Http\Controllers\Mail;

use App\Http\Controllers\Controller;
use App\Models\EmailAccount;
use App\Services\EmailAccountProvisioner;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class EmailAccountController extends Controller
{
    public function index(Request $request): View
    {
        $accounts = $request->user()->emailAccounts()->orderBy('email')->get();

        return view('mail.accounts.index', compact('accounts'));
    }

    public function create(Request $request): View
    {
        return view('mail.accounts.create', [
            'manual' => (bool) old('manual', session('manual')),
            'discovery' => session('discovery_preview'),
        ]);
    }

    public function store(Request $request, EmailAccountProvisioner $provisioner): RedirectResponse
    {
        $manual = $request->boolean('manual');

        $baseRules = [
            'email' => ['required', 'email', 'max:255'],
            'mailbox_password' => ['required', 'string'],
            'profile_name' => ['nullable', 'string', 'max:255'],
            'display_name' => ['nullable', 'string', 'max:255'],
            'reply_to' => ['nullable', 'email'],
            'organization' => ['nullable', 'string', 'max:255'],
        ];

        $manualRules = [
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
            'delete_behavior' => ['nullable', 'in:move_trash,mark_deleted,delete_immediate'],
            'signature_html' => ['nullable', 'string'],
            'signature_use_html' => ['sometimes', 'boolean'],
        ];

        $validated = $request->validate(array_merge($baseRules, $manual ? $manualRules : []));

        $resolved = $provisioner->resolveIncoming($request, $validated, $manual);
        if (($resolved['incoming'] ?? null) === null) {
            return back()
                ->withInput()
                ->with('manual', true)
                ->withErrors(['email' => $resolved['error'] ?? 'Неуспешно откриване на настройки.']);
        }

        try {
            $account = $provisioner->createAccount($request->user(), $validated, $resolved['incoming'], $request);
        } catch (\Throwable $e) {
            report($e);

            return back()
                ->withInput()
                ->with('manual', true)
                ->withErrors([
                    'mailbox_password' => $e->getMessage(),
                ]);
        }

        return redirect()
            ->route('mail.folder', [$account, 'inbox'])
            ->with('status', 'Имейл акаунтът е добавен успешно.');
    }

    public function destroy(Request $request, EmailAccount $emailAccount, EmailAccountProvisioner $provisioner): RedirectResponse
    {
        $this->authorize('delete', $emailAccount);

        try {
            $provisioner->deleteAccount($emailAccount);
        } catch (\Throwable $e) {
            report($e);

            return redirect()
                ->back()
                ->withErrors(['delete' => 'Неуспешно премахване на акаунта. Опитайте отново.']);
        }

        $next = $request->user()->emailAccounts()->first();

        return $next
            ? redirect()->route('mail.folder', [$next, 'inbox'])->with('status', 'Акаунтът е премахнат.')
            : redirect()->route('email-accounts.create')->with('status', 'Акаунтът е премахнат.');
    }
}
