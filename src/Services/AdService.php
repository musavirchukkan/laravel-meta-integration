<?php

namespace Musavirchukkan\LaravelMetaIntegration\Services;

use GuzzleHttp\Client as GuzzleClient;
use Illuminate\Support\Facades\Log;
use Musavirchukkan\LaravelMetaIntegration\Exceptions\MetaApiException;

class AdService
{
    protected $client;
    protected $apiVersion;

    public function __construct(GuzzleClient $client)
    {
        $this->client = $client;
        $this->apiVersion = config('meta.api_version', 'v18.0');
    }

    /**
     * Get ad accounts for user
     */
    public function getAdAccounts(string $token, array $fields = []): array
    {
        $defaultFields = ['account_id', 'name', 'account_status', 'business_name', 'currency'];
        $fields = !empty($fields) ? $fields : $defaultFields;

        try {
            $response = $this->client->get("https://graph.facebook.com/{$this->apiVersion}/me/adaccounts", [
                'query' => [
                    'access_token' => $token,
                    'fields' => implode(',', $fields)
                ]
            ]);

            return json_decode($response->getBody()->getContents(), true);

        } catch (\Exception $e) {
            Log::error('Failed to fetch ad accounts', ['error' => $e->getMessage()]);
            throw new MetaApiException('Failed to fetch ad accounts: ' . $e->getMessage());
        }
    }

    /**
     * Get campaigns for an ad account
     */
    public function getCampaigns(string $token, string $adAccountId, array $fields = [], array $params = []): array
    {
        $defaultFields = ['id', 'name', 'objective', 'status', 'buying_type', 'spend_cap', 'start_time', 'stop_time'];
        $fields = !empty($fields) ? $fields : $defaultFields;

        $defaultParams = [
            'limit' => 100,
            'date_preset' => 'last_30d'
        ];
        $params = array_merge($defaultParams, $params);

        try {
            $response = $this->client->get("https://graph.facebook.com/{$this->apiVersion}/act_{$adAccountId}/campaigns", [
                'query' => array_merge([
                    'access_token' => $token,
                    'fields' => implode(',', $fields)
                ], $params)
            ]);

            return json_decode($response->getBody()->getContents(), true);

        } catch (\Exception $e) {
            Log::error('Failed to fetch campaigns', [
                'error' => $e->getMessage(),
                'ad_account_id' => $adAccountId
            ]);
            throw new MetaApiException('Failed to fetch campaigns: ' . $e->getMessage());
        }
    }

    /**
     * Get ad sets for a campaign
     */
    public function getAdSets(string $token, string $campaignId, array $fields = [], array $params = []): array
    {
        $defaultFields = ['id', 'name', 'status', 'daily_budget', 'lifetime_budget', 'targeting', 'bid_strategy'];
        $fields = !empty($fields) ? $fields : $defaultFields;

        try {
            $response = $this->client->get("https://graph.facebook.com/{$this->apiVersion}/{$campaignId}/adsets", [
                'query' => array_merge([
                    'access_token' => $token,
                    'fields' => implode(',', $fields)
                ], $params)
            ]);

            return json_decode($response->getBody()->getContents(), true);

        } catch (\Exception $e) {
            Log::error('Failed to fetch ad sets', [
                'error' => $e->getMessage(),
                'campaign_id' => $campaignId
            ]);
            throw new MetaApiException('Failed to fetch ad sets: ' . $e->getMessage());
        }
    }

    /**
     * Get ads for an ad set
     */
    public function getAds(string $token, string $adSetId, array $fields = [], array $params = []): array
    {
        $defaultFields = ['id', 'name', 'status', 'creative', 'tracking_specs', 'conversion_specs'];
        $fields = !empty($fields) ? $fields : $defaultFields;

        try {
            $response = $this->client->get("https://graph.facebook.com/{$this->apiVersion}/{$adSetId}/ads", [
                'query' => array_merge([
                    'access_token' => $token,
                    'fields' => implode(',', $fields)
                ], $params)
            ]);

            return json_decode($response->getBody()->getContents(), true);

        } catch (\Exception $e) {
            Log::error('Failed to fetch ads', [
                'error' => $e->getMessage(),
                'ad_set_id' => $adSetId
            ]);
            throw new MetaApiException('Failed to fetch ads: ' . $e->getMessage());
        }
    }

    /**
     * Get lead forms for an ad account
     */
    public function getLeadForms(string $token, string $adAccountId, array $fields = []): array
    {
        $defaultFields = ['id', 'name', 'status', 'page_id', 'question_page_custom_headline'];
        $fields = !empty($fields) ? $fields : $defaultFields;

        try {
            $response = $this->client->get("https://graph.facebook.com/{$this->apiVersion}/act_{$adAccountId}/leadgen_forms", [
                'query' => [
                    'access_token' => $token,
                    'fields' => implode(',', $fields)
                ]
            ]);

            return json_decode($response->getBody()->getContents(), true);

        } catch (\Exception $e) {
            Log::error('Failed to fetch lead forms', [
                'error' => $e->getMessage(),
                'ad_account_id' => $adAccountId
            ]);
            throw new MetaApiException('Failed to fetch lead forms: ' . $e->getMessage());
        }
    }
}