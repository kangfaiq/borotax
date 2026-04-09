<?php

namespace App\Providers\Filament;

use App\Filament\Pages\Auth\Login;
use App\Filament\Pages\Dashboard;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\MenuItem;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\View\PanelsRenderHook;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use App\Http\Middleware\EnsureSingleSession;
use Illuminate\Session\Middleware\AuthenticateSession;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->viteTheme('resources/css/filament/admin/theme.css')
            ->login(Login::class)
            ->brandName('Borotax')
            ->favicon(asset('favicon.ico'))
            ->colors([
                'primary' => Color::Blue,
                'danger' => Color::Rose,
                'warning' => Color::Amber,
                'success' => Color::Emerald,
                'info' => Color::Sky,
            ])
            ->font('Inter')
            ->topNavigation(fn (): bool => auth()->user()?->usesTopNavigation() ?? true)
            ->sidebarCollapsibleOnDesktop(fn (): bool => auth()->user()?->navigation_mode === 'sidebar')
            ->navigationGroups([
                'Pendaftaran',
                'Laporan Petugas',
                'Verifikasi',
                'Laporan',
                'Program',
                'Master Data',
                'CMS',
                'Sistem',
            ])
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
            ->pages([
                Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets')
            ->widgets([])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
                EnsureSingleSession::class,
            ])
            ->userMenuItems([
                'navigation-toggle' => MenuItem::make()
                    ->label(fn (): string => auth()->user()?->usesTopNavigation()
                        ? 'Gunakan Sidebar'
                        : 'Gunakan Top Bar')
                    ->icon(fn (): string => auth()->user()?->usesTopNavigation()
                        ? 'heroicon-o-bars-3'
                        : 'heroicon-o-bars-3-bottom-left')
                    ->url(fn (): string => route('filament.toggle-navigation'))
                    ->sort(1),
            ])
            ->databaseNotifications()
            ->databaseNotificationsPolling('30s')
            ->viteTheme('resources/css/filament/admin/theme.css')
            ->renderHook(
                PanelsRenderHook::HEAD_END,
                fn () => view('filament.partials.admin-styles'),
            )
            ->renderHook(
                PanelsRenderHook::SIDEBAR_FOOTER,
                fn () => view('filament.partials.sidebar-footer-collapse'),
            );
    }
}
