<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\Concerns\ApiResponses;
use App\Http\Controllers\Controller;
use App\Models\EmailAccount;
use App\Services\EmailAccountProvisioner;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class EmailAccountApiController extends Controller
{
    use ApiResponses;

    public function index(Request $request): JsonResponse
    {
        try {
            $accounts = $request->user()->emailAccounts()->orderBy('email')->get();

            return $this->ok(['email_accounts' => $accounts]);
        } catch (\Throwable $e) {
            report($e);

            return $this->fail('Неуспешно зареждане на акаунти.', 500);
        }
    }

    public function store(Request $request, EmailAccountProvisioner $provisioner): JsonResponse
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

        try {
            $validated = $request->validate(array_merge($baseRules, $manual ? $manualRules : []));
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Невалидни данни.',
                'errors' => $e->errors(),
            ], 422);
        }

        try {
            $resolved = $provisioner->resolveIncoming($request, $validated, $manual);
            if (($resolved['incoming'] ?? null) === null) {
                return $this->fail($resolved['error'] ?? 'Не са намерени настройки.', 422);
            }

            $account = $provisioner->createAccount($request->user(), $validated, $resolved['incoming'], $request);

            return $this->ok(['email_account' => $account], 201);
        } catch (\RuntimeException $e) {
            return $this->fail($e->getMessage(), 422);
        } catch (\Throwable $e) {
            report($e);

            return $this->fail('Грешка при създаване на имейл акаунт.', 500);
        }
    }

    public function destroy(Request $request, EmailAccount $account, EmailAccountProvisioner $provisioner): JsonResponse
    {
        $this->authorize('delete', $account);

        try {
            $provisioner->deleteAccount($account);

            return $this->ok(['message' => 'Имейл акаунтът е премахнат.']);
        } catch (\Throwable $e) {
            report($e);

            return $this->fail('Неуспешно изтриване на акаунта.', 500);
        }
    }
}
