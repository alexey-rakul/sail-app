<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\Payment\PaymentManager;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(PaymentManager::class, function ($app) {
            return new PaymentManager($app);
        });
    
        // Алиас для удобного вызова через app('payment')
        $this->app->alias(PaymentManager::class, 'payment');
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
