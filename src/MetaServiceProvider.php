<?php

namespace MusavirChukkan\MetaIntegration;

use Illuminate\Support\ServiceProvider;
use MusavirChukkan\MetaIntegration\Support\MetaClient;
use MusavirChukkan\MetaIntegration\Services\ConnectionService;
use MusavirChukkan\MetaIntegration\Contracts\ConnectionServiceInterface;

class MetaServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Register config
        $this->mergeConfigFrom(
            __DIR__.'/../config/meta.php', 'meta'
        );

        // Register MetaClient
        $this->app->singleton(MetaClient::class, function ($app) {
            return new MetaClient();
        });

        // Register Connection Service
        $this->app->singleton(ConnectionServiceInterface::class, ConnectionService::class);

        // Register Main Service
        $this->app->singleton('meta', function ($app) {
            return new Meta(
                $app->make(ConnectionServiceInterface::class)
            );
        });
    }

    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/meta.php' => config_path('meta.php'),
            ], 'meta-config');
        }
    }
}