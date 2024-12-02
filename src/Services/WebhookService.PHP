<?php

namespace Musavirchukkan\LaravelMetaIntegration\Services;

use GuzzleHttp\Client as GuzzleClient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Musavirchukkan\LaravelMetaIntegration\Exceptions\MetaApiException;

class WebhookService
{
    protected $client;
    protected $appSecret;
    protected $verifyToken;

    public function __construct(GuzzleClient $client)
    {
        $this->client = $client;
        $this->appSecret = config('meta.app_secret');
        $this->verifyToken = config('meta.webhook_verify_token');
    }

    /**
     * Verify webhook request from Meta
     */
    public function verifyRequest(Request $request): bool|string
    {
        // Handle verification request
        if ($request->has('hub_mode') && $request->get('hub_mode') === 'subscribe') {
            if ($request->get('hub_verify_token') === $this->verifyToken) {
                return $request->get('hub_challenge');
            }
            return false;
        }

        // Verify signature for webhook payload
        $signature = $request->header('x-hub-signature-256');
        if (!$signature) {
            return false;
        }

        $payload = $request->getContent();
        $expectedSignature = 'sha256=' . hash_hmac('sha256', $payload, $this->appSecret);
        
        return hash_equals($expectedSignature, $signature);
    }

    /**
     * Subscribe to webhooks
     */
    public function subscribe(string $pageToken, string $pageId, array $fields = []): array
    {
        $defaultFields = [
            'leadgen',
            'ads_insights',
            'page_feed',
            'page_conversations'
        ];

        $fields = !empty($fields) ? $fields : $defaultFields;

        try {
            $response = $this->client->post("https://graph.facebook.com/v18.0/$pageId/subscribed_apps", [
                'query' => [
                    'access_token' => $pageToken,
                    'subscribed_fields' => implode(',', $fields)
                ]
            ]);

            $result = json_decode($response->getBody()->getContents(), true);
            
            if (!($result['success'] ?? false)) {
                throw new MetaApiException('Failed to subscribe to webhooks');
            }

            return $result;

        } catch (\Exception $e) {
            Log::error('Webhook subscription failed', [
                'error' => $e->getMessage(),
                'page_id' => $pageId,
                'fields' => $fields
            ]);
            throw new MetaApiException('Webhook subscription failed: ' . $e->getMessage());
        }
    }

    /**
     * Process webhook payload
     */
    public function processWebhook(array $payload): array
    {
        $processedEvents = [];

        try {
            foreach ($payload['entry'] ?? [] as $entry) {
                $changes = $entry['changes'] ?? [];
                foreach ($changes as $change) {
                    $processedEvents[] = $this->processEvent($change);
                }
            }

            return [
                'success' => true,
                'events' => $processedEvents
            ];

        } catch (\Exception $e) {
            Log::error('Webhook processing failed', [
                'error' => $e->getMessage(),
                'payload' => $payload
            ]);
            
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'events' => $processedEvents
            ];
        }
    }

    /**
     * Process individual webhook event
     */
    protected function processEvent(array $change): array
    {
        $value = $change['value'] ?? [];
        $field = $change['field'] ?? 'unknown';

        return [
            'field' => $field,
            'event_type' => $this->determineEventType($field, $value),
            'data' => $value,
            'timestamp' => time()
        ];
    }

    /**
     * Determine event type from webhook data
     */
    protected function determineEventType(string $field, array $value): string
    {
        switch ($field) {
            case 'leadgen':
                return 'lead_generated';
            case 'ads_insights':
                return 'insights_updated';
            case 'page_feed':
                return 'feed_updated';
            case 'page_conversations':
                return 'conversation_update';
            default:
                return 'unknown';
        }
    }

    /**
     * Unsubscribe from webhooks
     */
    public function unsubscribe(string $pageToken, string $pageId): bool
    {
        try {
            $response = $this->client->delete("https://graph.facebook.com/v18.0/$pageId/subscribed_apps", [
                'query' => [
                    'access_token' => $pageToken
                ]
            ]);

            $result = json_decode($response->getBody()->getContents(), true);
            return $result['success'] ?? false;

        } catch (\Exception $e) {
            Log::error('Webhook unsubscription failed', [
                'error' => $e->getMessage(),
                'page_id' => $pageId
            ]);
            return false;
        }
    }
}