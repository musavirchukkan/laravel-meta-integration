<?php

namespace MusavirChukkan\MetaIntegration\Support;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Facades\Cache;
use MusavirChukkan\MetaIntegration\Exceptions\MetaException;

class MetaClient
{
    protected Client $client;
    protected string $baseUrl;
    protected array $config;
    protected ?string $accessToken = null;

    public function __construct()
    {
        $this->config = config('meta');
        $this->baseUrl = rtrim($this->config['graph_url'], '/') . '/' . $this->config['graph_version'] . '/';
        $this->client = new Client(['base_uri' => $this->baseUrl]);
    }

    public function setAccessToken(string $token): self
    {
        $this->accessToken = $token;
        return $this;
    }

    public function request(string $method, string $endpoint, array $params = [])
    {
        if ($this->accessToken) {
            $params['access_token'] = $this->accessToken;
        }

        try {
            $response = $this->client->request($method, $endpoint, [
                'query' => $method === 'GET' ? $params : null,
                'json' => $method !== 'GET' ? $params : null,
                'headers' => [
                    'Accept' => 'application/json',
                ]
            ]);

            return json_decode($response->getBody()->getContents(), true);
        } catch (GuzzleException $e) {
            throw new MetaException(
                $e->getMessage(),
                $e->getCode(),
                $e->hasResponse() ? json_decode($e->getResponse()->getBody()->getContents(), true) : []
            );
        }
    }
}