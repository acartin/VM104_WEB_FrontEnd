<?php

namespace App\Providers;

use Filament\Support\Facades\FilamentView;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;

class FilamentCustomAssetsServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        FilamentView::registerRenderHook(
            'panels::body.end',
            fn (): string => Blade::render(<<<'HTML'
                <script>
                    // Global Lucide initialization for performance
                    document.addEventListener('DOMContentLoaded', function() {
                        if (window.lucide) {
                            lucide.createIcons();
                        }
                    });

                    // Re-scan after Livewire updates
                    document.addEventListener('livewire:navigated', function() {
                        if (window.lucide) {
                            lucide.createIcons();
                        }
                    });

                    // Re-scan after table updates
                    if (window.Livewire) {
                        Livewire.hook('commit', ({ component, succeed }) => {
                            succeed(() => {
                                if (window.lucide) {
                                    setTimeout(() => lucide.createIcons(), 100);
                                }
                            });
                        });
                    }
                </script>
            HTML)
        );
    }
}
