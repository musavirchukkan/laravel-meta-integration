<?php

namespace MusavirChukkan\MetaIntegration\Contracts;

interface InsightsServiceInterface
{
    public function getCampaignInsights(string $campaignId, string $token, array $metrics = [], string $datePreset = 'last_30_days'): array;
    public function getAdInsights(string $adId, string $token, array $metrics = [], string $datePreset = 'last_30_days'): array;
    public function getAccountInsights(string $accountId, string $token, array $metrics = [], string $datePreset = 'last_30_days'): array;
    public function getPageInsights(string $pageId, string $token, array $metrics = [], string $datePreset = 'last_30_days'): array;
}