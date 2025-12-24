<?php

namespace App\Providers\Filament;

use Filament\Http\Middleware\Authenticate;
use BezhanSalleh\FilamentShield\FilamentShieldPlugin;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Widgets;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
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
            ->login(\App\Filament\Pages\Auth\AdminLogin::class)
            ->brandName('')
            ->colors([
                'primary' => Color::Indigo,
            ])
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
            ->pages([
                Pages\Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets')
            ->widgets([
                //
            ])
            ->renderHook(
                'panels::page.start',
                fn (): string => '<style nonce="'.bin2hex(random_bytes(16)).'">
                    .fi-header-heading { display: none !important; }
                    .fi-sidebar-header { display: none !important; }
                    
                    /* Light mode only - background and borders */
                    html:not(.dark) .fi-body {
                        background-color: #f2f6f9 !important;
                    }
                    
                    html:not(.dark) .fi-sidebar {
                        border-right-color: #d4d4d4 !important;
                    }
                    
                    html:not(.dark) .fi-section,
                    html:not(.dark) .fi-ta-ctn,
                    html:not(.dark) .fi-fo-component-ctn,
                    html:not(.dark) [class*="border"] {
                        border-color: #d4d4d4 !important;
                    }
                    
                    /* Active menu item - white background with border */
                    html:not(.dark) .fi-sidebar-item-button.fi-active,
                    html:not(.dark) .fi-sidebar-item.fi-active > .fi-sidebar-item-button {
                        background-color: #ffffff !important;
                        border: 1px solid #c0c0c0 !important;
                        border-radius: 0.5rem !important;
                    }
                </style>',
            )
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
            ->plugins([
                FilamentShieldPlugin::make(),
            ])
            ->authMiddleware([
                Authenticate::class,
            ])
            ->authGuard('admin');
    }
}
