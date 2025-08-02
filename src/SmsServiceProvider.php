<?php

namespace RmdMostakim\BdSms;

use Illuminate\Support\ServiceProvider;

class SmsServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/bdsms.php', 'bdsms');

        $this->app->singleton('bdsms', function ($app) {
            return new SmsManager($app);
        });
    }

    public function boot()
    {
        // Load migrations ALWAYS
        $this->loadMigrationsFrom(__DIR__ . '/../migrations');

        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../config/bdsms.php' => config_path('bdsms.php'),
            ], 'config');
        }
    }
}
