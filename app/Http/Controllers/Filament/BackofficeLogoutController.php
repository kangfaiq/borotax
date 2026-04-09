<?php

namespace App\Http\Controllers\Filament;

use App\Domain\Auth\Support\SingleSessionManager;
use Filament\Auth\Http\Responses\Contracts\LogoutResponse;
use Filament\Facades\Filament;

class BackofficeLogoutController
{
    public function __invoke(): LogoutResponse
    {
        $user = Filament::auth()->user();

        if ($user) {
            SingleSessionManager::clearCurrentSession($user, request());
        }

        Filament::auth()->logout();
        session()->migrate(true);
        session()->regenerateToken();

        return app(LogoutResponse::class);
    }
}