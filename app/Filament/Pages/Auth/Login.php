<?php

namespace App\Filament\Pages\Auth;

use App\Domain\Auth\Support\SingleSessionManager;
use Filament\Facades\Filament;
use Filament\Auth\Http\Responses\Contracts\LoginResponse;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Component;
use Illuminate\Validation\ValidationException;

class Login extends \Filament\Auth\Pages\Login
{
    protected string $view = 'filament.pages.auth.login';

    protected static string $layout = 'filament-panels::components.layout.base';

    public function authenticate(): ?LoginResponse
    {
        $response = parent::authenticate();

        // Cek jika user yang login adalah wajib_pajak
        $user = Filament::auth()->user();
        if ($user && $user->role === 'wajib_pajak') {
            Filament::auth()->logout();
            request()->session()->migrate(true);
            request()->session()->regenerateToken();

            throw ValidationException::withMessages([
                'data.email' => 'Login Wajib Pajak berada di halaman ' . url('/login') . '. Silakan login di halaman tersebut.',
            ]);
        }

        // Update last login timestamp
        if ($user) {
            $user->update(['last_login_at' => now()]);

            $singleSessionResult = SingleSessionManager::startWebSession(
                $user,
                (string) ($this->data['password'] ?? ''),
                request(),
                'admin_panel',
                'web',
            );

            if ($singleSessionResult['replaced_session_notice']) {
                Notification::make()
                    ->info()
                    ->title('Sesi Lama Diakhiri')
                    ->body($singleSessionResult['replaced_session_notice'])
                    ->send();
            }
        }

        return $response;
    }

    protected function getEmailFormComponent(): Component
    {
        return TextInput::make('email')
            ->label(__('filament-panels::auth/pages/login.form.email.label'))
            ->email()
            ->required()
            ->autocomplete('username')
            ->autofocus();
    }

    protected function getPasswordFormComponent(): Component
    {
        return TextInput::make('password')
            ->label(__('filament-panels::auth/pages/login.form.password.label'))
            ->password()
            ->revealable(filament()->arePasswordsRevealable())
            ->autocomplete('current-password')
            ->required();
    }
}

