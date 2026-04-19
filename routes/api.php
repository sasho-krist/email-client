<?php

use App\Http\Controllers\Api\AuthApiController;
use App\Http\Controllers\Api\EmailAccountApiController;
use App\Http\Controllers\Api\MailboxApiController;
use App\Http\Controllers\Api\MailDiscoverApiController;
use App\Http\Controllers\Api\MailSettingsApiController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function (): void {
    Route::post('/login', [AuthApiController::class, 'login']);

    Route::middleware('auth:sanctum')->group(function (): void {
        Route::post('/logout', [AuthApiController::class, 'logout']);
        Route::get('/user', [AuthApiController::class, 'user']);

        Route::post('/mail/discover', [MailDiscoverApiController::class, 'store']);

        Route::get('/email-accounts', [EmailAccountApiController::class, 'index']);
        Route::post('/email-accounts', [EmailAccountApiController::class, 'store']);
        Route::delete('/email-accounts/{account}', [EmailAccountApiController::class, 'destroy']);

        Route::get('/email-accounts/{account}/folders/{folder}/messages', [MailboxApiController::class, 'index'])
            ->where('folder', 'inbox|sent|spam|trash');
        Route::get('/email-accounts/{account}/folders/{folder}/messages/{uid}', [MailboxApiController::class, 'show'])
            ->where('folder', 'inbox|sent|spam|trash');
        Route::delete('/email-accounts/{account}/folders/{folder}/messages/{uid}', [MailboxApiController::class, 'destroy'])
            ->where('folder', 'inbox|sent|spam|trash');

        Route::patch('/mail/settings', [MailSettingsApiController::class, 'update']);
    });
});
