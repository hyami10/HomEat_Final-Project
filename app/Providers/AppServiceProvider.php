<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Vite;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        if (config('app.force_https')) {
            \Illuminate\Support\Facades\URL::forceScheme('https');
        }
        
        Blade::directive('nonce', function () {
            return "<?php echo 'nonce=\"' . app('csp-nonce') . '\"'; ?>";
        });
        
        Blade::directive('cspNonceValue', function () {
            return "<?php echo app('csp-nonce'); ?>";
        });
        
        \Illuminate\Support\Facades\View::composer('layouts.navigation', function ($view) {
            if (auth()->check()) {
                $userId = auth()->id();
                $cartCount = cache()->remember("cart_count_{$userId}", 60, function () use ($userId) {
                    return \App\Models\Cart::where('user_id', $userId)->count();
                });
                $view->with('cartCount', $cartCount);
            } else {
                $view->with('cartCount', 0);
            }
        });
        
        \App\Models\User::observe(\App\Observers\UserObserver::class);
    }
}
