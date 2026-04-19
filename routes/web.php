<?php

use App\Http\Controllers\Mail\EmailAccountController;
use App\Http\Controllers\Mail\MailboxController;
use App\Http\Controllers\Mail\MailSettingsController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', [PageController::class, 'home'])->name('home');

Route::get('/privacy', [PageController::class, 'privacy'])->name('privacy');
Route::get('/terms', [PageController::class, 'terms'])->name('terms');
Route::get('/faq', [PageController::class, 'faq'])->name('faq');
Route::get('/api-docs', [PageController::class, 'apiGuide'])->name('api.docs');

Route::get('/dashboard', function () {
    $user = auth()->user();
    $first = $user?->emailAccounts()->first();

    return $first
        ? redirect()->route('mail.folder', [$first, 'inbox'])
        : redirect()->route('email-accounts.create');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::middleware(['auth', 'verified'])->group(function () {
    Route::resource('email-accounts', EmailAccountController::class)->only(['index', 'create', 'store', 'destroy']);

    Route::prefix('mail')->name('mail.')->group(function () {
        Route::get('settings', [MailSettingsController::class, 'edit'])->name('settings');
        Route::patch('settings', [MailSettingsController::class, 'update'])->name('settings.update');
        Route::post('settings/rediscover-folders', [MailSettingsController::class, 'rediscoverFolders'])->name('settings.rediscover-folders');

        Route::get('{account}/folder/{folder}', [MailboxController::class, 'folder'])
            ->where('folder', 'inbox|sent|spam|trash')
            ->name('folder');

        Route::get('{account}/folder/{folder}/message/{uid}', [MailboxController::class, 'message'])
            ->whereNumber('uid')
            ->name('message');

        Route::delete('{account}/folder/{folder}/message/{uid}', [MailboxController::class, 'destroyMessage'])
            ->whereNumber('uid')
            ->name('message.destroy');
    });
});

require __DIR__.'/auth.php';
