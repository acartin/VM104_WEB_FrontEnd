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
                'danger' => Color::Red,
                'gray' => Color::Slate,
                'info' => Color::Blue,
                'success' => Color::Green,
                'warning' => Color::Orange,
                'away' => '#f87171',
                'offline' => '#b91c1c',
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
                fn (): string => '', // DISABLED CUSTOM STYLES
            )
            ->renderHook(
                'panels::head.end',
                function (): string {
                    try {
                        $user = auth()->user();
                        $theme = $user?->theme ?? 'default';
                        
                        // If theme is default, DO NOT load the plugin's CSS.
                        // Let Filament use its native styles (which respect our colors() configuration).
                        if ($theme === 'default') {
                            return '';
                        }
                        
                        $cssPath = match($theme) {
                            'dracula' => 'css/hasnayeen/themes/dracula.css',
                            'nord' => 'css/hasnayeen/themes/nord.css',
                            'sunset' => 'css/hasnayeen/themes/sunset.css',
                            'forest' => 'css/themes/forest.css',
                            'datasync' => 'css/themes/datasync.css',
                            default => '', // Should not happen given the check above
                        };
                        
                        $url = asset($cssPath) . '?v=' . time();
                        
                        $script = '';
                        $isDark = in_array($theme, ['dracula', 'sunset', 'nord', 'forest']);
                        
                        if ($isDark) {
                            $script = '<script>
                                document.documentElement.classList.add("dark");
                                localStorage.setItem("theme", "dark");
                                new MutationObserver(() => {
                                    if (!document.documentElement.classList.contains("dark")) {
                                        document.documentElement.classList.add("dark");
                                    }
                                }).observe(document.documentElement, { attributes: true, attributeFilter: ["class"] });
                            </script>';
                        } else {
                            $script = '';
                        }

                        return "{$script}<link rel=\"stylesheet\" href=\"{$url}\" data-theme=\"{$theme}\" />";
                    } catch (\Throwable $e) {
                         return '';
                    }
                }
            )
            ->renderHook(
                \Filament\View\PanelsRenderHook::GLOBAL_SEARCH_AFTER,
                fn (): string => \Illuminate\Support\Facades\Blade::render('@livewire(\App\Livewire\ThemeSwitcher::class)'),
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
            ->plugins([
                FilamentShieldPlugin::make(),
                \Hasnayeen\Themes\ThemesPlugin::make()
                    ->canViewThemesPage(fn () => false),
            ])
            ->authMiddleware([
                Authenticate::class,
            ])
            ->authGuard('web');
    }
}
