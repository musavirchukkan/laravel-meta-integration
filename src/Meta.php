<?php

namespace Musavirchukkan\LaravelMetaIntegration;

use Musavirchukkan\LaravelMetaIntegration\Contracts\MetaClientInterface;
use Musavirchukkan\LaravelMetaIntegration\Services\AuthenticationService;
use Musavirchukkan\LaravelMetaIntegration\Services\ConnectionService;
use Musavirchukkan\LaravelMetaIntegration\Services\PageService;
use Musavirchukkan\LaravelMetaIntegration\Services\FormService;
use Musavirchukkan\LaravelMetaIntegration\Services\AdService;
use Musavirchukkan\LaravelMetaIntegration\Services\WebhookService;
use Musavirchukkan\LaravelMetaIntegration\Services\InsightsService;

class Meta
{
    protected $client;
    protected $auth;
    protected $connection;
    protected $pages;
    protected $forms;
    protected $ads;
    protected $webhooks;
    protected $insights;

    public function __construct(MetaClientInterface $client)
    {
        $this->client = $client;
        $this->initializeServices();
    }

    protected function initializeServices()
    {
        $this->auth = new AuthenticationService($this->client);
        $this->connection = new ConnectionService($this->client);
        $this->pages = new PageService($this->client);
        $this->forms = new FormService($this->client);
        $this->ads = new AdService($this->client);
        $this->webhooks = new WebhookService($this->client);
        $this->insights = new InsightsService($this->client);
    }

    public function connect()
    {
        return $this->auth->getConnectionUrl();
    }

    public function handleCallback($request)
    {
        return $this->auth->handleCallback($request);
    }

    public function pages()
    {
        return $this->pages;
    }

    public function forms()
    {
        return $this->forms;
    }

    public function ads()
    {
        return $this->ads;
    }

    public function webhooks()
    {
        return $this->webhooks;
    }

    public function insights()
    {
        return $this->insights;
    }

    public function checkPermissions(array $permissions = [])
    {
        return $this->auth->checkPermissions($permissions);
    }

    public function deauthorize()
    {
        return $this->auth->deauthorize();
    }
}