<?php

namespace App\Providers;

use App\Models\EmailAccount;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        App::setLocale(config('app.locale'));

        if ($this->app->environment('testing')) {
            Config::set('database.default', 'sqlite');
            Config::set('database.connections.sqlite.database', database_path('testing.sqlite'));
        }

        Schema::defaultStringLength(191);

        $accountResolver = function (string $value) {
            if (! auth()->check()) {
                abort(401);
            }

            return EmailAccount::query()
                ->where('user_id', auth()->id())
                ->whereKey($value)
                ->firstOrFail();
        };

        Route::bind('account', $accountResolver);
        Route::bind('email_account', $accountResolver);
    }
}
