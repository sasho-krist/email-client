<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class PasswordController extends Controller
{
    /**
     * Update the user's password.
     */
    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validateWithBag('updatePassword', [
            'current_password' => ['required', 'current_password'],
            'password' => ['required', Password::defaults(), 'confirmed'],
        ]);

        try {
            DB::transaction(function () use ($request, $validated): void {
                $request->user()->update([
                    'password' => Hash::make($validated['password']),
                ]);
            });
        } catch (\Throwable $e) {
            report($e);

            return back()->withErrors([
                'password' => 'Възникна грешка при смяна на паролата. Опитайте отново.',
            ], 'updatePassword');
        }

        return back()->with('status', 'password-updated');
    }
}
