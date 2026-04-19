<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\Concerns\ApiResponses;
use App\Http\Controllers\Controller;
use App\Models\EmailAccount;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class MailSettingsApiController extends Controller
{
    use ApiResponses;

    public function update(Request $request): JsonResponse
    {
        try {
            $account = $this->resolveAccount($request);
            $this->authorize('update', $account);

            $tab = $request->input('tab', 'server');

            $rules = match ($tab) {
                'server' => [
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
                ],
                'signature' => [
                    'signature_html' => ['nullable', 'string'],
                    'signature_use_html' => ['sometimes', 'boolean'],
                ],
                'profile' => [
                    'profile_name' => ['nullable', 'string', 'max:255'],
                    'account_color' => ['nullable', 'regex:/^#[0-9a-fA-F]{6}$/'],
                    'display_name' => ['nullable', 'string', 'max:255'],
                    'reply_to' => ['nullable', 'email'],
                    'organization' => ['nullable', 'string', 'max:255'],
                ],
                'display' => [
                    'inbox_group_by' => ['required', 'in:none,date'],
                ],
                'reply' => [],
                default => throw ValidationException::withMessages([
                    'tab' => ['Невалиден раздел. Използвайте: server, signature, profile, display, reply.'],
                ]),
            };

            $validated = $tab === 'reply' ? [] : $request->validate($rules);

            DB::transaction(function () use ($request, $account, $tab, $validated): void {
                if ($tab === 'server') {
                    $account->fill(collect($validated)->except(['mailbox_password'])->all());

                    if (! empty($validated['mailbox_password'])) {
                        $account->mailbox_password = $validated['mailbox_password'];
                    }

                    $account->save();
                }

                if ($tab === 'signature') {
                    $account->signature_html = $validated['signature_html'] ?? null;
                    $account->signature_use_html = $request->boolean('signature_use_html', true);
                    $account->save();
                }

                if ($tab === 'profile') {
                    $account->update($validated);
                }

                if ($tab === 'display') {
                    $pref = $request->user()->mailPreferenceOrCreate();
                    $pref->update($validated);
                }

                if ($tab === 'reply') {
                    $pref = $request->user()->mailPreferenceOrCreate();
                    $pref->update([
                        'reply_include_quote' => $request->boolean('reply_include_quote'),
                        'reply_top_posting' => $request->boolean('reply_top_posting'),
                    ]);
                }
            });

            return $this->ok([
                'message' => 'Настройките са запазени.',
                'email_account' => $account->fresh(),
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Невалидни данни.',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Throwable $e) {
            report($e);

            return $this->fail('Неуспешно записване на настройки.', 500);
        }
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
