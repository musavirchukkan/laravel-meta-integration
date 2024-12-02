<?php

namespace MusavirChukkan\MetaIntegration;

use Illuminate\Support\ServiceProvider;
use MusavirChukkan\MetaIntegration\Support\MetaClient;
use MusavirChukkan\MetaIntegration\Services\ConnectionService;
use MusavirChukkan\MetaIntegration\Contracts\ConnectionServiceInterface;
use MusavirChukkan\MetaIntegration\Contracts\InsightsServiceInterface;
use MusavirChukkan\MetaIntegration\Contracts\WebhookServiceInterface;
use MusavirChukkan\MetaIntegration\Facades\Meta;
use MusavirChukkan\MetaIntegration\Services\InsightsService;
use MusavirChukkan\MetaIntegration\Services\WebhookService;

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

        // Register Insights Service
        $this->app->singleton(InsightsServiceInterface::class, InsightsService::class);

        // Register Webhook Service
        $this->app->singleton(WebhookServiceInterface::class, WebhookService::class);

        // Update Meta binding
        $this->app->singleton('meta', function ($app) {
            return new Meta(
                $app->make(ConnectionServiceInterface::class),
                $app->make(InsightsServiceInterface::class),
                $app->make(WebhookServiceInterface::class)
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