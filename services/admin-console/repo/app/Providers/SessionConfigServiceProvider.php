<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Config;

class SessionConfigServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Determine the session configuration based on the request URL
        $path = request()->path();
        
        // Check for specific panel paths
        if (str_starts_with($path, 'admin')) {
            config(['session.table' => 'admin_sessions']);
            config(['session.cookie' => 'admin_session']);
            config(['session.path' => '/']);
        } elseif (str_starts_with($path, 'app')) {
            config(['session.table' => 'app_sessions']);
            config(['session.cookie' => 'app_session']);
            config(['session.path' => '/']);
        } elseif (str_starts_with($path, 'livewire')) {
            // For livewire requests, attempt to determine context from Referer
            $referer = request()->header('Referer');
            if ($referer) {
                $refererPath = parse_url($referer, PHP_URL_PATH);
                // Remove leading slash if present for cleaner checking
                $refererPath = ltrim($refererPath, '/');
                
                if (str_starts_with($refererPath, 'admin')) {
                    config(['session.table' => 'admin_sessions']);
                    config(['session.cookie' => 'admin_session']);
                    config(['session.path' => '/']);
                } elseif (str_starts_with($refererPath, 'app')) {
                    config(['session.table' => 'app_sessions']);
                    config(['session.cookie' => 'app_session']);
                    config(['session.path' => '/']);
                }
                // Fallback or other handling if needed
            }
        }
    }
}
