<?php

namespace App\Providers;

use App\Helpers\SettingsHelper;
use Illuminate\Support\ServiceProvider;

class SettingsServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(SettingsHelper::class, fn () => new SettingsHelper());
        $this->app->alias(SettingsHelper::class, 'settings');
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}