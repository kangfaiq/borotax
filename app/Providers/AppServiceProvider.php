<?php

namespace App\Providers;

use App\Http\Controllers\Filament\BackofficeLogoutController;
use Filament\Auth\Http\Controllers\LogoutController as FilamentLogoutController;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(FilamentLogoutController::class, BackofficeLogoutController::class);
    }

    public function boot(): void
    {
        //
    }
}
