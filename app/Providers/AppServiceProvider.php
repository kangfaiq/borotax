<?php

namespace App\Providers;

use App\Http\Controllers\Filament\BackofficeLogoutController;
use Filament\Auth\Http\Controllers\LogoutController as FilamentLogoutController;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(FilamentLogoutController::class, BackofficeLogoutController::class);
    }

    public function boot(): void
    {
        RateLimiter::for('histori-pajak', fn (Request $request) =>
            Limit::perMinutes(15, 5)->by($request->ip())
        );
    }
}
