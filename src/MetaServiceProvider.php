<?php

namespace Musavirchukkan\LaravelMetaIntegration;

use Illuminate\Support\ServiceProvider;
use Musavirchukkan\LaravelMetaIntegration\Contracts\MetaClientInterface;
use Musavirchukkan\LaravelMetaIntegration\Support\MetaClient;
use MusavirChukkan\MetaIntegration\Facades\Meta;
use MusavirChukkan\MetaIntegration\Support\MetaClient as SupportMetaClient;

class MetaServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->mergeConfigFrom(
            __DIR__.'/../config/meta.php', 'meta'
        );

        $this->app->singleton(MetaClientInterface::class, function ($app) {
            return new SupportMetaClient(
                config('meta.app_id'),
                config('meta.app_secret'),
                config('meta.api_version')
            );
        });

        $this->app->singleton('meta', function ($app) {
            return new Meta($app->make(MetaClientInterface::class));
        });
    }

    public function boot()
    {
        $this->publishes([
            __DIR__.'/../config/meta.php' => config_path('meta.php'),
        ], 'config');

        $this->publishes([
            __DIR__.'/../database/migrations/' => database_path('migrations'),
        ], 'migrations');

        $this->loadRoutesFrom(__DIR__.'/../routes/web.php');
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
    }
}