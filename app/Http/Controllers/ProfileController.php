<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): View
    {
        return view('profile.edit', [
            'user' => $request->user(),
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        try {
            DB::transaction(function () use ($request): void {
                $validated = $request->validated();
                $removeAvatar = $request->boolean('remove_avatar');
                $avatarFile = $request->file('avatar');

                unset($validated['avatar'], $validated['remove_avatar']);

                /** @var User $user */
                $user = $request->user();

                if ($removeAvatar && $user->avatar_path) {
                    Storage::disk('public')->delete($user->avatar_path);
                    $user->avatar_path = null;
                }

                if ($avatarFile !== null) {
                    if ($user->avatar_path) {
                        Storage::disk('public')->delete($user->avatar_path);
                    }
                    $user->avatar_path = $avatarFile->store('avatars', 'public');
                }

                $user->fill($validated);

                if ($user->isDirty('email')) {
                    $user->email_verified_at = null;
                }

                $user->save();
            });
        } catch (\Throwable $e) {
            report($e);

            return Redirect::route('profile.edit')->withErrors([
                'email' => 'Възникна грешка при записа на профила. Опитайте отново.',
            ]);
        }

        return Redirect::route('profile.edit')->with('status', 'profile-updated');
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $userKey = (int) $request->user()->getKey();

        try {
            DB::transaction(function () use ($userKey): void {
                $user = User::query()->findOrFail($userKey);

                if ($user->avatar_path) {
                    Storage::disk('public')->delete($user->avatar_path);
                }

                $user->delete();
            });
        } catch (\Throwable $e) {
            report($e);

            return Redirect::route('profile.edit')->withErrors([
                'password' => 'Неуспешно изтриване на акаунта. Опитайте отново.',
            ], 'userDeletion');
        }

        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }
}
