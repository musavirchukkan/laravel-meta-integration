<?php

namespace MusavirChukkan\MetaIntegration\Contracts;

interface WebhookServiceInterface
{
    public function subscribe(string $pageId, string $token, array $fields = []): array;
    public function unsubscribe(string $pageId, string $token): bool;
    public function validateWebhook(array $payload): bool;
    public function handleWebhook(array $payload): array;
}