<?php

namespace MusavirChukkan\MetaIntegration\Tests\Unit;

use MusavirChukkan\MetaIntegration\Tests\TestCase;
use MusavirChukkan\MetaIntegration\Services\ConnectionService;
use MusavirChukkan\MetaIntegration\Support\MetaClient;
use Mockery;

class ConnectionServiceTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->client = Mockery::mock(MetaClient::class);
        $this->service = new ConnectionService($this->client);
    }

    /** @test */
    public function it_generates_connection_url()
    {
        $url = $this->service->getConnectionUrl(['return_to' => 'dashboard']);
        
        $this->assertIsString($url);
        $this->assertStringContainsString('facebook.com', $url);
        $this->assertStringContainsString('return_to', $url);
    }

    /** @test */
    public function it_validates_valid_token()
    {
        $this->client->shouldReceive('setAccessToken')
            ->once()
            ->with('valid-token')
            ->andReturnSelf();

        $this->client->shouldReceive('request')
            ->once()
            ->with('GET', 'me')
            ->andReturn(['id' => '123']);

        $this->assertTrue($this->service->validateToken('valid-token'));
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}