<?php

namespace Musavirchukkan\LaravelMetaIntegration\Services;

use GuzzleHttp\Client as GuzzleClient;
use Illuminate\Support\Facades\Log;
use Musavirchukkan\LaravelMetaIntegration\Exceptions\MetaApiException;

class FormService
{
    protected $client;
    protected $apiVersion;

    public function __construct(GuzzleClient $client)
    {
        $this->client = $client;
        $this->apiVersion = config('meta.api_version', 'v18.0');
    }

    /**
     * Get form details
     */
    public function getForm(string $token, string $formId, array $fields = []): array
    {
        $defaultFields = [
            'id',
            'name',
            'status',
            'questions',
            'qualifiers',
            'locale',
            'page_id',
            'privacy_policy_url'
        ];
        $fields = !empty($fields) ? $fields : $defaultFields;

        try {
            $response = $this->client->get("https://graph.facebook.com/{$this->apiVersion}/{$formId}", [
                'query' => [
                    'access_token' => $token,
                    'fields' => implode(',', $fields)
                ]
            ]);

            return json_decode($response->getBody()->getContents(), true);

        } catch (\Exception $e) {
            Log::error('Failed to fetch form details', [
                'error' => $e->getMessage(),
                'form_id' => $formId
            ]);
            throw new MetaApiException('Failed to fetch form details: ' . $e->getMessage());
        }
    }

    /**
     * Get leads for a specific form
     */
    public function getFormLeads(string $token, string $formId, array $params = []): array
    {
        $defaultParams = [
            'fields' => 'created_time,id,field_data,campaign_id,adset_id,ad_id',
            'limit' => 100
        ];
        $params = array_merge($defaultParams, $params);

        try {
            $response = $this->client->get("https://graph.facebook.com/{$this->apiVersion}/{$formId}/leads", [
                'query' => array_merge([
                    'access_token' => $token
                ], $params)
            ]);

            return json_decode($response->getBody()->getContents(), true);

        } catch (\Exception $e) {
            Log::error('Failed to fetch form leads', [
                'error' => $e->getMessage(),
                'form_id' => $formId
            ]);
            throw new MetaApiException('Failed to fetch form leads: ' . $e->getMessage());
        }
    }

    /**
     * Get specific lead details
     */
    public function getLeadDetails(string $token, string $leadId): array
    {
        try {
            $response = $this->client->get("https://graph.facebook.com/{$this->apiVersion}/{$leadId}", [
                'query' => [
                    'access_token' => $token,
                    'fields' => 'created_time,id,field_data,campaign_id,adset_id,ad_id,form_id'
                ]
            ]);

            return json_decode($response->getBody()->getContents(), true);

        } catch (\Exception $e) {
            Log::error('Failed to fetch lead details', [
                'error' => $e->getMessage(),
                'lead_id' => $leadId
            ]);
            throw new MetaApiException('Failed to fetch lead details: ' . $e->getMessage());
        }
    }
}