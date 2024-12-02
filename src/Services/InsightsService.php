<?php

namespace MusavirChukkan\MetaIntegration\Services;

use MusavirChukkan\MetaIntegration\Support\MetaClient;
use MusavirChukkan\MetaIntegration\Contracts\InsightsServiceInterface;
use MusavirChukkan\MetaIntegration\Exceptions\MetaException;
use Illuminate\Support\Facades\Cache;

class InsightsService implements InsightsServiceInterface
{
    protected array $defaultMetrics = [
        'impressions',
        'reach',
        'clicks',
        'spend',
        'cpc',
        'ctr',
        'conversions',
        'cost_per_conversion'
    ];

    public function __construct(
        protected MetaClient $client
    ) {}

    public function getCampaignInsights(
        string $campaignId, 
        string $token, 
        array $metrics = [], 
        string $datePreset = 'last_30_days'
    ): array {
        $cacheKey = "meta_campaign_insights_{$campaignId}_{$datePreset}";

        return Cache::remember($cacheKey, 3600, function () use ($campaignId, $token, $metrics, $datePreset) {
            $this->client->setAccessToken($token);

            $response = $this->client->request('GET', "{$campaignId}/insights", [
                'fields' => $this->prepareMetrics($metrics),
                'date_preset' => $datePreset,
                'level' => 'campaign'
            ]);

            return $this->formatInsightsResponse($response);
        });
    }

    public function getAdInsights(
        string $adId, 
        string $token, 
        array $metrics = [], 
        string $datePreset = 'last_30_days'
    ): array {
        $cacheKey = "meta_ad_insights_{$adId}_{$datePreset}";

        return Cache::remember($cacheKey, 3600, function () use ($adId, $token, $metrics, $datePreset) {
            $this->client->setAccessToken($token);

            $response = $this->client->request('GET', "{$adId}/insights", [
                'fields' => $this->prepareMetrics($metrics),
                'date_preset' => $datePreset,
                'level' => 'ad'
            ]);

            return $this->formatInsightsResponse($response);
        });
    }

    public function getAccountInsights(
        string $accountId, 
        string $token, 
        array $metrics = [], 
        string $datePreset = 'last_30_days'
    ): array {
        $cacheKey = "meta_account_insights_{$accountId}_{$datePreset}";

        return Cache::remember($cacheKey, 3600, function () use ($accountId, $token, $metrics, $datePreset) {
            $this->client->setAccessToken($token);

            $response = $this->client->request('GET', "act_{$accountId}/insights", [
                'fields' => $this->prepareMetrics($metrics),
                'date_preset' => $datePreset,
                'level' => 'account'
            ]);

            return $this->formatInsightsResponse($response);
        });
    }

    public function getPageInsights(
        string $pageId, 
        string $token, 
        array $metrics = [], 
        string $datePreset = 'last_30_days'
    ): array {
        $cacheKey = "meta_page_insights_{$pageId}_{$datePreset}";

        return Cache::remember($cacheKey, 3600, function () use ($pageId, $token, $metrics, $datePreset) {
            $this->client->setAccessToken($token);

            $response = $this->client->request('GET', "{$pageId}/insights", [
                'fields' => $this->prepareMetrics($metrics),
                'date_preset' => $datePreset,
                'period' => 'day'
            ]);

            return $this->formatInsightsResponse($response);
        });
    }

    protected function prepareMetrics(array $metrics): string
    {
        return implode(',', !empty($metrics) ? $metrics : $this->defaultMetrics);
    }

    protected function formatInsightsResponse(array $response): array
    {
        if (!isset($response['data']) || empty($response['data'])) {
            return [];
        }

        return $response['data'];
    }
}