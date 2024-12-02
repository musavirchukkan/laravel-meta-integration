<?php

namespace MusavirChukkan\MetaIntegration\Tests\Unit;

use MusavirChukkan\MetaIntegration\Tests\TestCase;
use MusavirChukkan\MetaIntegration\Services\InsightsService;
use MusavirChukkan\MetaIntegration\Support\MetaClient;
use Mockery;

class InsightsServiceTest extends TestCase
{
    protected $client;
    protected $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->client = Mockery::mock(MetaClient::class);
        $this->service = new InsightsService($this->client);
    }

    /** @test */
    public function it_gets_campaign_insights()
    {
        $campaignId = '123456789';
        $token = 'test-token';
        $expectedResponse = [
            'data' => [
                [
                    'impressions' => '1000',
                    'clicks' => '100',
                    'spend' => '50.00'
                ]
            ]
        ];

        $this->client->shouldReceive('setAccessToken')
            ->once()
            ->with($token)
            ->andReturnSelf();

        $this->client->shouldReceive('request')
            ->once()
            ->with('GET', "{$campaignId}/insights", Mockery::subset([
                'date_preset' => 'last_30_days',
                'level' => 'campaign'
            ]))
            ->andReturn($expectedResponse);

        $insights = $this->service->getCampaignInsights($campaignId, $token);

        $this->assertEquals($expectedResponse['data'], $insights);
    }

    /** @test */
    public function it_handles_empty_insights_response()
    {
        $this->client->shouldReceive('setAccessToken')->andReturnSelf();
        $this->client->shouldReceive('request')->andReturn(['data' => []]);

        $insights = $this->service->getAdInsights('123', 'token');

        $this->assertEmpty($insights);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}