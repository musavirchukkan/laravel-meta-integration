<?php

namespace Musavirchukkan\LaravelMetaIntegration\Services;

use GuzzleHttp\Client as GuzzleClient;
use Illuminate\Support\Facades\Log;
use Musavirchukkan\LaravelMetaIntegration\Exceptions\MetaApiException;

class InsightsService
{
    protected $client;
    protected $apiVersion;

    protected $defaultMetrics = [
        'impressions',
        'clicks',
        'spend',
        'reach',
        'cpm',
        'cpc',
        'ctr',
        'frequency',
        'actions',
        'cost_per_action_type'
    ];

    public function __construct(GuzzleClient $client)
    {
        $this->client = $client;
        $this->apiVersion = config('meta.api_version', 'v18.0');
    }

    /**
     * Get campaign insights
     */
    public function getCampaignInsights(
        string $token,
        string $campaignId,
        array $metrics = [],
        array $params = []
    ): array {
        return $this->getInsights($token, $campaignId, 'campaign', $metrics, $params);
    }

    /**
     * Get adset insights
     */
    public function getAdSetInsights(
        string $token,
        string $adSetId,
        array $metrics = [],
        array $params = []
    ): array {
        return $this->getInsights($token, $adSetId, 'adset', $metrics, $params);
    }

    /**
     * Get ad insights
     */
    public function getAdInsights(
        string $token,
        string $adId,
        array $metrics = [],
        array $params = []
    ): array {
        return $this->getInsights($token, $adId, 'ad', $metrics, $params);
    }

    /**
     * Get account insights
     */
    public function getAccountInsights(
        string $token,
        string $accountId,
        array $metrics = [],
        array $params = []
    ): array {
        return $this->getInsights($token, $accountId, 'account', $metrics, $params);
    }

    /**
     * Generic insights fetcher
     */
    protected function getInsights(
        string $token,
        string $id,
        string $level,
        array $metrics = [],
        array $params = []
    ): array {
        $metrics = !empty($metrics) ? $metrics : $this->defaultMetrics;
        
        $defaultParams = [
            'time_range' => [
                'since' => date('Y-m-d', strtotime('-30 days')),
                'until' => date('Y-m-d')
            ],
            'time_increment' => 1
        ];

        $params = array_merge($defaultParams, $params);

        try {
            $endpoint = "https://graph.facebook.com/{$this->apiVersion}/{$id}/insights";
            
            $response = $this->client->get($endpoint, [
                'query' => array_merge([
                    'access_token' => $token,
                    'fields' => implode(',', $metrics),
                    'level' => $level
                ], $params)
            ]);

            $result = json_decode($response->getBody()->getContents(), true);

            return $this->processInsightsResponse($result);

        } catch (\Exception $e) {
            Log::error("Failed to fetch {$level} insights", [
                'error' => $e->getMessage(),
                'id' => $id
            ]);
            throw new MetaApiException("Failed to fetch {$level} insights: " . $e->getMessage());
        }
    }

    /**
     * Process insights response
     */
    protected function processInsightsResponse(array $response): array
    {
        $data = $response['data'] ?? [];
        
        // Process action types if present
        foreach ($data as &$day) {
            if (isset($day['actions'])) {
                $day['actions_by_type'] = collect($day['actions'])
                    ->keyBy('action_type')
                    ->map(fn($action) => $action['value'])
                    ->toArray();
            }
        }

        return [
            'data' => $data,
            'paging' => $response['paging'] ?? null
        ];
    }
}