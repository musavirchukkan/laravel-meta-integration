<?php

namespace MusavirChukkan\MetaIntegration\Services;

use Laravel\Socialite\Facades\Socialite;
use MusavirChukkan\MetaIntegration\Support\MetaClient;
use MusavirChukkan\MetaIntegration\Contracts\ConnectionServiceInterface;
use MusavirChukkan\MetaIntegration\Exceptions\MetaException;

class ConnectionService implements ConnectionServiceInterface
{
    protected array $defaultPermissions = [
        'email',
        'leads_retrieval',
        'pages_manage_metadata',
        'pages_show_list',
        'pages_manage_ads',
        'business_management',
        'pages_read_engagement',
        'ads_management',
        'ads_read',
        'read_insights'
    ];

    public function __construct(
        protected MetaClient $client
    ) {}

    public function getConnectionUrl(array $state = [], array $additionalPermissions = []): string
    {
        $permissions = array_unique(array_merge(
            $this->defaultPermissions,
            $additionalPermissions
        ));

        return Socialite::driver('facebook')
            ->usingGraphVersion(config('meta.graph_version'))
            ->scopes($permissions)
            ->with(['state' => urlencode(json_encode($state))])
            ->redirectUrl(config('meta.redirect_url'))
            ->redirect()
            ->getTargetUrl();
    }

    public function handleCallback(): array
    {
        try {
            $user = Socialite::driver('facebook')->user();
            $token = $user->token;
            $userId = $user->getId();
        } catch (\Exception $e) {
            // Fallback to token exchange
            $token = $this->exchangeToken(request('code'));
            $userId = $this->getUserId($token);
        }

        return [
            'token' => $token,
            'user_id' => $userId,
            'state' => json_decode(urldecode(request('state')), true)
        ];
    }

    public function exchangeToken(string $code): string
    {
        $response = $this->client->request('GET', 'oauth/access_token', [
            'client_id' => config('meta.client_id'),
            'client_secret' => config('meta.client_secret'),
            'redirect_uri' => config('meta.redirect_url'),
            'code' => $code
        ]);

        if (!isset($response['access_token'])) {
            throw new MetaException('Failed to exchange code for token');
        }

        return $response['access_token'];
    }

    public function validateToken(string $token): bool
    {
        try {
            $this->client->setAccessToken($token);
            $this->client->request('GET', 'me');
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    protected function getUserId(string $token): string
    {
        $response = $this->client
            ->setAccessToken($token)
            ->request('GET', 'me', ['fields' => 'id']);

        return $response['id'];
    }
}