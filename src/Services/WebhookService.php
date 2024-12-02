<?php

namespace MusavirChukkan\MetaIntegration\Services;

use MusavirChukkan\MetaIntegration\Support\MetaClient;
use MusavirChukkan\MetaIntegration\Contracts\WebhookServiceInterface;
use MusavirChukkan\MetaIntegration\Exceptions\MetaException;
use Illuminate\Support\Facades\Log;

class WebhookService implements WebhookServiceInterface
{
    protected array $defaultFields = [
        'leadgen',
        'feed',
        'messages',
        'messaging_postbacks'
    ];

    public function __construct(
        protected MetaClient $client
    ) {}

    public function subscribe(string $pageId, string $token, array $fields = []): array
    {
        $this->client->setAccessToken($token);

        // Subscribe to page webhooks
        $subscribeResponse = $this->client->request('POST', "{$pageId}/subscribed_apps", [
            'subscribed_fields' => implode(',', !empty($fields) ? $fields : $this->defaultFields)
        ]);

        if (!($subscribeResponse['success'] ?? false)) {
            throw new MetaException('Failed to subscribe to page webhooks');
        }

        // Get subscription details
        return $this->client->request('GET', "{$pageId}/subscribed_apps");
    }

    public function unsubscribe(string $pageId, string $token): bool
    {
        $this->client->setAccessToken($token);

        $response = $this->client->request('DELETE', "{$pageId}/subscribed_apps");

        return $response['success'] ?? false;
    }

    public function validateWebhook(array $payload): bool
    {
        if (!isset($payload['entry'][0]['changes'][0])) {
            return false;
        }

        $expectedToken = config('meta.app_token');
        $headerToken = request()->header('X-Hub-Signature-256');

        if (!$headerToken) {
            return false;
        }

        $signature = hash_hmac(
            'sha256',
            json_encode($payload),
            $expectedToken
        );

        return hash_equals("sha256={$signature}", $headerToken);
    }

    public function handleWebhook(array $payload): array
    {
        if (!$this->validateWebhook($payload)) {
            throw new MetaException('Invalid webhook signature');
        }

        try {
            $event = $payload['entry'][0]['changes'][0];
            $eventType = $event['field'] ?? 'unknown';

            Log::channel('meta')->info('Webhook received', [
                'type' => $eventType,
                'payload' => $payload
            ]);

            return [
                'success' => true,
                'type' => $eventType,
                'data' => $event['value']
            ];

        } catch (\Exception $e) {
            Log::channel('meta')->error('Webhook handling failed', [
                'error' => $e->getMessage(),
                'payload' => $payload
            ]);

            throw new MetaException('Failed to handle webhook: ' . $e->getMessage());
        }
    }
}