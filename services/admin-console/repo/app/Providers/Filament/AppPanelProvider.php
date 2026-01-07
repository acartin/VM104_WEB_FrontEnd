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
            ->default()
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
                \App\Filament\App\Resources\LeadResource\Widgets\LeadStatsOverview::class,
            ])
            ->renderHook(
                'panels::page.start',
                fn (): string => '', // DISABLED OLD LAYOUT STYLES
            )
            ->renderHook(
                'panels::head.end',
                fn (): string => '<style>
                    /* Thermal Scale Helpers */
                    .thermal-extreme { color: #ef4444 !important; filter: drop-shadow(0 0 8px rgba(239, 68, 68, 0.7)) !important; }
                    .thermal-extreme svg { stroke: #ef4444 !important; }
                    
                    .thermal-high { color: #f97316 !important; }
                    .thermal-high svg { stroke: #f97316 !important; }
                    
                    .thermal-mid { color: #f59e0b !important; }
                    .thermal-mid svg { stroke: #f59e0b !important; }
                    
                    .thermal-low { color: #eab308 !important; }
                    .thermal-low svg { stroke: #eab308 !important; }
                    
                    /* Financial Scale (Emerald) */
                    .thermal-finance-extreme { color: #34d399 !important; filter: drop-shadow(0 0 8px rgba(52, 211, 153, 0.7)) !important; }
                    .thermal-finance-extreme svg { stroke: #34d399 !important; }
                    .thermal-finance-high { color: #10b981 !important; }
                    .thermal-finance-high svg { stroke: #10b981 !important; }

                    /* TIMELINE BADGES V2.2 - Narrower & Softer Borders */
                    .t-badge-base { display: inline-flex !important; align-items: center !important; justify-content: center !important; width: 110px !important; height: 22px !important; padding: 0 !important; font-weight: 400 !important; font-size: 0.60rem !important; border-radius: 6px !important; border: 1px solid rgba(0,0,0,0.1) !important; text-transform: uppercase !important; letter-spacing: 0.5px !important; white-space: nowrap !important; line-height: 1 !important; }
                    .t-badge-base * { font-weight: 400 !important; font-size: 0.60rem !important; color: inherit !important; }

                    .t-inmediato { color: #ef4444 !important; border-color: rgba(239, 68, 68, 0.4) !important; background: rgba(239, 68, 68, 0.05) !important; filter: drop-shadow(0 0 3px rgba(239, 68, 68, 0.1)) !important; }
                    .t-caliente { color: #f87171 !important; border-color: rgba(248, 113, 113, 0.4) !important; background: rgba(248, 113, 113, 0.05) !important; }
                    .t-tibio { color: #fb923c !important; border-color: rgba(251, 146, 60, 0.4) !important; background: rgba(251, 146, 60, 0.05) !important; }
                    .t-medio { color: #fbbf24 !important; border-color: rgba(251, 191, 36, 0.4) !important; background: rgba(251, 191, 36, 0.05) !important; }
                    .t-indefinido { color: #3b82f6 !important; border-color: rgba(59, 130, 246, 0.4) !important; background: rgba(59, 130, 246, 0.05) !important; }
                    .t-largo { color: #94a3b8 !important; border-color: rgba(148, 163, 184, 0.4) !important; background: rgba(148, 163, 184, 0.05) !important; }
                    .t-frio { color: #475569 !important; border-color: rgba(71, 85, 105, 0.4) !important; background: rgba(71, 85, 105, 0.05) !important; }

                    .t-inmediato svg { stroke: #ef4444 !important; }
                    .t-caliente svg { stroke: #f87171 !important; }
                    .t-tibio svg { stroke: #fb923c !important; }
                    .t-medio svg { stroke: #fbbf24 !important; }
                    .t-indefinido svg { stroke: #3b82f6 !important; }
                    .t-largo svg { stroke: #94a3b8 !important; }
                    .t-frio svg { stroke: #475569 !important; }
                    
                    /* Hot Lead Glow Overlay */
                    .hot-lead-glow { filter: drop-shadow(0 0 8px rgba(239, 68, 68, 0.6)); }

                    /* GLOW DOWN: Safer selector to soften bright white text in Dark Mode */
                    html.dark .fi-in-text-item [class*="text-white"] {
                        color: #9ca3af !important; /* Gray 400 */
                    }
                </style>',
            )
            ->renderHook(
                'panels::head.end',
                function (): string {
                    try {
                        $user = auth()->user();
                        $theme = $user?->theme ?? 'default';
                        
                        if ($theme === 'default') {
                             return '';
                        }
                        
                        // Map themes to their asset paths (standard paths for this plugin)
                        $cssPath = match($theme) {
                            'dracula' => 'css/hasnayeen/themes/dracula.css',
                            'nord' => 'css/hasnayeen/themes/nord.css',
                            'sunset' => 'css/hasnayeen/themes/sunset.css',
                            'forest' => 'css/themes/forest.css',
                            'datasync' => 'css/themes/datasync.css',
                            default => '',
                        };
                        
                        $url = asset($cssPath) . '?v=' . time();
                        
                        // Force dark mode class injection script for dark themes
                        $script = '';
                        $isDark = in_array($theme, ['dracula', 'sunset', 'nord', 'forest']);
                        
                        if ($isDark) {
                            $script = '<script>
                                document.documentElement.classList.add("dark");
                                localStorage.setItem("theme", "dark");
                                // Watch for changes and re-add if removed by Filament
                                new MutationObserver(() => {
                                    if (!document.documentElement.classList.contains("dark")) {
                                        document.documentElement.classList.add("dark");
                                    }
                                }).observe(document.documentElement, { attributes: true, attributeFilter: ["class"] });
                            </script>';
                        } else {
                            // For default theme, let Filament handle dark/light mode natively
                            $script = '';
                        }

                        return "{$script}<link rel=\"stylesheet\" href=\"{$url}\" data-theme=\"{$theme}\" />";
                    } catch (\Throwable $e) {
                         return '';
                    }
                }
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
                fn (): string => \Illuminate\Support\Facades\Blade::render('
                    <div class="flex items-center gap-x-6">
                        @livewire(\App\Livewire\UserStatusSelector::class)
                        @livewire(\App\Livewire\ThemeSwitcher::class)
                    </div>
                '),
            )
            ->plugin(
                \Hasnayeen\Themes\ThemesPlugin::make()
                    ->canViewThemesPage(fn () => false)
            )
            ->middleware([
                \Hasnayeen\Themes\Http\Middleware\SetTheme::class,
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
