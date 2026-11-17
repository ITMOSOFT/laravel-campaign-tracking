<?php

namespace ItmoSoft\CampaignTracking;

use Illuminate\Support\ServiceProvider;

class CampaignTrackingServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/campaign-tracking.php', 'itmosoft-campaign-tracking');
    }

    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');

        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/campaign-tracking.php' => config_path('itmosoft-campaign-tracking.php'),
            ], 'itmosoft-campaign-tracking-config');
        }
    }
}
