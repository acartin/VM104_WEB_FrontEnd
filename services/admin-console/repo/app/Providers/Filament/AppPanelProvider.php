<?php

namespace App\Providers\Filament;

use Filament\Http\Middleware\Authenticate;
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

use App\Models\Client;

class AppPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->id('app')
            ->path('app')
            ->login(\App\Filament\App\Pages\Auth\ClientLogin::class)
            ->brandName('')
            ->tenant(Client::class, slugAttribute: 'slug')
            ->colors([
                'primary' => '#2ca65b',
                'success' => Color::Green,
                'warning' => Color::Amber,
                'danger' => Color::Red,
                'info' => Color::Blue,
                'gray' => Color::Slate,
                'away' => '#f87171',
                'offline' => '#b91c1c',
            ])
            ->discoverResources(in: app_path('Filament/App/Resources'), for: 'App\\Filament\\App\\Resources')
            ->discoverPages(in: app_path('Filament/App/Pages'), for: 'App\\Filament\\App\\Pages')
            ->pages([
                Pages\Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/App/Widgets'), for: 'App\\Filament\\App\\Widgets')
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

                    /* Table Compression */
                    .fi-ta-record td {
                        padding-top: 0.25rem !important;
                        padding-bottom: 0.25rem !important;
                    }

                    .fi-ta-cell {
                        vertical-align: middle !important;
                    }

                    /* Table Header Styling - Professional & Subdued */
                    .fi-ta-header-cell-label {
                        text-transform: uppercase !important;
                        letter-spacing: 0.05em !important;
                        color: #6b7280 !important; /* text-gray-500 */
                        font-size: 0.8rem !important;
                        font-weight: 700 !important;
                    }

                    /* TIMELINE BADGES V2.0 - Square & Bordered (v2.1 Weight Fix) */
                    .t-badge-base { display: inline-flex !important; align-items: center !important; justify-content: center !important; width: 130px !important; height: 22px !important; padding: 0 !important; font-weight: 500 !important; font-size: 9px !important; border-radius: 6px !important; border-width: 1px !important; border-style: solid !important; text-transform: uppercase !important; letter-spacing: 0.5px !important; white-space: nowrap !important; line-height: 1 !important; }
                    .t-badge-base * { font-weight: 500 !important; color: inherit !important; }
                    .t-inmediato { color: #ef4444 !important; border-color: #ef4444 !important; background: rgba(239, 68, 68, 0.05) !important; filter: drop-shadow(0 0 3px rgba(239, 68, 68, 0.2)) !important; }
                    .t-caliente { color: #f87171 !important; border-color: #f87171 !important; background: rgba(248, 113, 113, 0.05) !important; }
                    .t-tibio { color: #fb923c !important; border-color: #fb923c !important; background: rgba(251, 146, 60, 0.05) !important; }
                    .t-medio { color: #fbbf24 !important; border-color: #fbbf24 !important; background: rgba(251, 191, 36, 0.05) !important; }
                    .t-indefinido { color: #3b82f6 !important; border-color: #3b82f6 !important; background: rgba(59, 130, 246, 0.05) !important; }
                    .t-largo { color: #94a3b8 !important; border-color: #94a3b8 !important; background: rgba(148, 163, 184, 0.05) !important; }
                    .t-frio { color: #475569 !important; border-color: #475569 !important; background: rgba(71, 85, 105, 0.05) !important; }

                    /* Thermal Scale Helpers */
                    .thermal-extreme { color: #ef4444 !important; filter: drop-shadow(0 0 8px rgba(239, 68, 68, 0.7)) !important; opacity: 1 !important; }
                    .thermal-extreme svg { stroke: #ef4444 !important; }
                    
                    .thermal-high { color: #f97316 !important; opacity: 1 !important; }
                    .thermal-high svg { stroke: #f97316 !important; }
                    
                    .thermal-mid { color: #f59e0b !important; opacity: 0.8 !important; }
                    .thermal-mid svg { stroke: #f59e0b !important; }
                    
                    .thermal-low { color: #eab308 !important; opacity: 0.6 !important; }
                    .thermal-low svg { stroke: #eab308 !important; }
                    
                    .thermal-none { color: #475569 !important; opacity: 0.4 !important; }
                    .thermal-none svg { stroke: #475569 !important; }

                    .thermal-finance-extreme { color: #34d399 !important; filter: drop-shadow(0 0 8px rgba(52, 211, 153, 0.7)) !important; opacity: 1 !important; }
                    .thermal-finance-extreme svg { stroke: #34d399 !important; }
                    
                    .thermal-finance-high { color: #10b981 !important; opacity: 1 !important; }
                    .thermal-finance-high svg { stroke: #10b981 !important; }

                    /* Hot Lead Glow Overlay */
                    .hot-lead-glow { filter: drop-shadow(0 0 8px rgba(239, 68, 68, 0.6)); }
                </style>',
            )
            ->renderHook(
                'panels::body.end',
                fn (): string => '<script src="https://unpkg.com/lucide@latest"></script><script>
                    const initLucide = () => {
                        if (window.lucide) {
                            window.lucide.createIcons();
                        }
                    };
                    document.addEventListener("DOMContentLoaded", initLucide);
                    document.addEventListener("filament-serving", initLucide);
                    document.addEventListener("livewire:navigated", initLucide);
                    document.addEventListener("livewire:init", () => {
                        Livewire.hook("morph.updated", (el, component) => {
                            initLucide();
                        });
                    });
                </script>',
            )
            ->renderHook(
                \Filament\View\PanelsRenderHook::GLOBAL_SEARCH_AFTER,
                fn (): string => \Illuminate\Support\Facades\Blade::render('@livewire(\App\Livewire\UserStatusSelector::class)'),
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
            ->authMiddleware([
                Authenticate::class,
            ])
            ->authGuard('web');
    }
}
