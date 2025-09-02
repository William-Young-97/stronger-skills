<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(QuoteService::class, function ($app) {
        return new QuoteService(
            jsonPath: resource_path('data/quotes.json'),
            timezone: config('app.timezone', 'Europe/London'),
            anchor: '2025-01-01',
            );
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
