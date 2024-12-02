<?php

namespace MusavirChukkan\MetaIntegration\Tests\Unit;

use MusavirChukkan\MetaIntegration\Tests\TestCase;
use MusavirChukkan\MetaIntegration\Services\WebhookService;
use MusavirChukkan\MetaIntegration\Support\MetaClient;
use MusavirChukkan\MetaIntegration\Exceptions\MetaException;
use Mockery;

class WebhookServiceTest extends TestCase
{
    protected $client;
    protected $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->client = Mockery::mock(MetaClient::class);
        $this->service = new WebhookService($this->client);
    }

    /** @test */
    public function it_subscribes_to_webhooks()
    {
        $pageId = '123456789';
        $token = 'test-token';

        $this->client->shouldReceive('setAccessToken')
            ->with($token)
            ->andReturnSelf();

        $this->client->shouldReceive('request')
            ->with('POST', "{$pageId}/subscribed_apps", Mockery::any())
            ->andReturn(['success' => true]);

        $this->client->shouldReceive('request')
            ->with('GET', "{$pageId}/subscribed_apps")
            ->andReturn(['data' => ['app_id' => '123']]);

        $result = $this->service->subscribe($pageId, $token);

        $this->assertArrayHasKey('data', $result);
    }

    /** @test */
    public function it_validates_webhook_signature()
    {
        config()->set('meta.app_token', 'test-secret');

        $payload = ['test' => 'data'];
        $signature = hash_hmac('sha256', json_encode($payload), 'test-secret');

        request()->headers->set('X-Hub-Signature-256', "sha256={$signature}");

        $this->assertTrue($this->service->validateWebhook($payload));
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}