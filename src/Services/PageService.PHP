<?php

namespace Musavirchukkan\LaravelMetaIntegration\Services;

use GuzzleHttp\Client as GuzzleClient;
use Illuminate\Support\Facades\Log;
use Musavirchukkan\LaravelMetaIntegration\Exceptions\MetaApiException;

class PageService
{
    protected $client;
    protected $apiVersion;

    public function __construct(GuzzleClient $client)
    {
        $this->client = $client;
        $this->apiVersion = config('meta.api_version', 'v18.0');
    }

    /**
     * Get all pages for user
     */
    public function getPages(string $token, array $fields = []): array
    {
        $defaultFields = ['id', 'name', 'access_token', 'tasks', 'category'];
        $fields = !empty($fields) ? $fields : $defaultFields;

        try {
            $response = $this->client->get("https://graph.facebook.com/{$this->apiVersion}/me/accounts", [
                'query' => [
                    'access_token' => $token,
                    'fields' => implode(',', $fields)
                ]
            ]);

            return json_decode($response->getBody()->getContents(), true);

        } catch (\Exception $e) {
            Log::error('Failed to fetch pages', ['error' => $e->getMessage()]);
            throw new MetaApiException('Failed to fetch pages: ' . $e->getMessage());
        }
    }

    /**
     * Get specific page details
     */
    public function getPage(string $token, string $pageId, array $fields = []): array
    {
        $defaultFields = ['id', 'name', 'category', 'fan_count', 'verification_status', 'about'];
        $fields = !empty($fields) ? $fields : $defaultFields;

        try {
            $response = $this->client->get("https://graph.facebook.com/{$this->apiVersion}/{$pageId}", [
                'query' => [
                    'access_token' => $token,
                    'fields' => implode(',', $fields)
                ]
            ]);

            return json_decode($response->getBody()->getContents(), true);

        } catch (\Exception $e) {
            Log::error('Failed to fetch page details', [
                'error' => $e->getMessage(),
                'page_id' => $pageId
            ]);
            throw new MetaApiException('Failed to fetch page details: ' . $e->getMessage());
        }
    }

    /**
     * Get page access token
     */
    public function getPageAccessToken(string $userToken, string $pageId): string
    {
        try {
            $response = $this->client->get("https://graph.facebook.com/{$this->apiVersion}/{$pageId}", [
                'query' => [
                    'access_token' => $userToken,
                    'fields' => 'access_token'
                ]
            ]);

            $result = json_decode($response->getBody()->getContents(), true);
            return $result['access_token'] ?? '';

        } catch (\Exception $e) {
            Log::error('Failed to fetch page access token', [
                'error' => $e->getMessage(),
                'page_id' => $pageId
            ]);
            throw new MetaApiException('Failed to fetch page access token: ' . $e->getMessage());
        }
    }
}