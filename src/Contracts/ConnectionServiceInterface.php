<?php

namespace MusavirChukkan\MetaIntegration\Contracts;

interface ConnectionServiceInterface
{
    public function getConnectionUrl(array $state = [], array $additionalPermissions = []): string;
    public function handleCallback(): array;
    public function exchangeToken(string $code): string;
    public function validateToken(string $token): bool;
}