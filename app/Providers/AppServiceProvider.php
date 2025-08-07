<?php

namespace App\Providers;

use App\Services\AzureGroupService;
use Illuminate\Support\ServiceProvider;
use Laravel\Socialite\Facades\Socialite;
use SocialiteProviders\Azure\Provider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Register AzureGroupService as a singleton
        $this->app->singleton(AzureGroupService::class, function ($app) {
            return new AzureGroupService;
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Configure Azure provider for Socialite
        $this->bootAzureSocialite();
    }

    /**
     * Bootstrap Azure Socialite provider
     */
    protected function bootAzureSocialite(): void
    {
        Socialite::extend('azure', function ($app) {
            $config = $app['config']['services.azure'];
            return Socialite::buildProvider(Provider::class, $config);
        });
    }
}
