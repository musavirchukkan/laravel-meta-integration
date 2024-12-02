<?php

namespace Musavirchukkan\LaravelMetaIntegration\Services;

use Laravel\Socialite\Facades\Socialite;
use GuzzleHttp\Client as GuzzleClient;
use Musavirchukkan\LaravelMetaIntegration\Exceptions\MetaAuthenticationException;
use Musavirchukkan\LaravelMetaIntegration\Exceptions\MetaPermissionException;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class AuthenticationService
{
    protected $client;
    protected $requiredPermissions = [
        'email',
        'leads_retrieval',
        'pages_manage_metadata',
        'pages_show_list',
        'pages_manage_ads',
        'business_management',
        'pages_read_engagement',
        'ads_management',
        'ads_read'
    ];

    public function __construct(GuzzleClient $client)
    {
        $this->client = $client;
    }

    /**
     * Get Facebook connection URL with proper scopes and state
     */
    public function getConnectionUrl(array $customData = [], array $additionalScopes = []): string
    {
        try {
            $state = $this->encodeState($customData);
            $scopes = array_merge($this->requiredPermissions, $additionalScopes);

            return Socialite::driver('facebook')
                ->usingGraphVersion(config('meta.api_version', 'v18.0'))
                ->scopes($scopes)
                ->with(['state' => $state])
                ->redirectUrl(config('meta.redirect_url'))
                ->redirect()
                ->getTargetUrl();

        } catch (\Exception $e) {
            Log::error('Meta connection URL generation failed', [
                'error' => $e->getMessage(),
                'custom_data' => $customData,
                'additional_scopes' => $additionalScopes
            ]);
            throw new MetaAuthenticationException('Failed to generate Meta connection URL: ' . $e->getMessage());
        }
    }

    /**
     * Handle the callback from Facebook and exchange code for token
     */
    public function handleCallback($request): array
    {
        try {
            // First try with Socialite
            $token = $this->getSocialiteToken($request);
            
            if (!$token) {
                // Fallback to manual token exchange
                $token = $this->exchangeCodeForToken($request->get('code'));
            }

            if (!$token) {
                throw new MetaAuthenticationException('Failed to obtain access token');
            }

            // Validate token and get long-lived token
            $longLivedToken = $this->getLongLivedToken($token);
            
            // Get user info
            $userInfo = $this->getUserInfo($longLivedToken);
            
            // Check permissions
            $this->validatePermissions($longLivedToken);

            return [
                'token' => $longLivedToken,
                'user_id' => $userInfo['id'] ?? null,
                'custom_data' => $this->decodeState($request->get('state')),
            ];

        } catch (\Exception $e) {
            Log::error('Meta authentication callback failed', [
                'error' => $e->getMessage(),
                'request' => $request->all()
            ]);
            throw new MetaAuthenticationException('Authentication failed: ' . $e->getMessage());
        }
    }

    /**
     * Get token using Socialite
     */
    protected function getSocialiteToken($request): ?string
    {
        try {
            $user = Socialite::driver('facebook')->user();
            return $user->token;
        } catch (\Exception $e) {
            Log::warning('Socialite token retrieval failed, falling back to manual exchange', [
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }

    /**
     * Manual code exchange for token
     */
    protected function exchangeCodeForToken(string $code): string
    {
        $response = $this->client->request('GET', 'https://graph.facebook.com/oauth/access_token', [
            'query' => [
                'client_id' => config('meta.app_id'),
                'client_secret' => config('meta.app_secret'),
                'redirect_uri' => config('meta.redirect_url'),
                'code' => $code
            ]
        ]);

        $data = json_decode($response->getBody()->getContents(), true);
        return $data['access_token'] ?? throw new MetaAuthenticationException('Invalid token response');
    }

    /**
     * Exchange short-lived token for long-lived token
     */
    protected function getLongLivedToken(string $shortLivedToken): string
    {
        $response = $this->client->request('GET', 'https://graph.facebook.com/oauth/access_token', [
            'query' => [
                'grant_type' => 'fb_exchange_token',
                'client_id' => config('meta.app_id'),
                'client_secret' => config('meta.app_secret'),
                'fb_exchange_token' => $shortLivedToken
            ]
        ]);

        $data = json_decode($response->getBody()->getContents(), true);
        return $data['access_token'] ?? throw new MetaAuthenticationException('Invalid long-lived token response');
    }

    /**
     * Validate that we have all required permissions
     */
    protected function validatePermissions(string $token): void
    {
        $response = $this->client->request('GET', 'https://graph.facebook.com/me/permissions', [
            'query' => ['access_token' => $token]
        ]);

        $permissions = json_decode($response->getBody()->getContents(), true);
        $grantedPermissions = collect($permissions['data'] ?? [])
            ->where('status', 'granted')
            ->pluck('permission')
            ->toArray();

        $missingPermissions = array_diff($this->requiredPermissions, $grantedPermissions);

        if (!empty($missingPermissions)) {
            throw new MetaPermissionException('Missing required permissions: ' . implode(', ', $missingPermissions));
        }
    }

    /**
     * Get basic user info
     */
    protected function getUserInfo(string $token): array
    {
        $response = $this->client->request('GET', 'https://graph.facebook.com/me', [
            'query' => [
                'access_token' => $token,
                'fields' => 'id,name,email'
            ]
        ]);

        return json_decode($response->getBody()->getContents(), true);
    }

    /**
     * Encode state data
     */
    protected function encodeState(array $data): string
    {
        return base64_encode(json_encode($data));
    }

    /**
     * Decode state data
     */
    protected function decodeState(?string $state): array
    {
        if (!$state) {
            return [];
        }
        return json_decode(base64_decode($state), true) ?? [];
    }

    /**
     * Check if token needs refresh
     */
    public function checkTokenValidity(string $token): bool
    {
        try {
            $response = $this->client->request('GET', 'https://graph.facebook.com/debug_token', [
                'query' => [
                    'input_token' => $token,
                    'access_token' => config('meta.app_id') . '|' . config('meta.app_secret')
                ]
            ]);

            $data = json_decode($response->getBody()->getContents(), true);
            return ($data['data']['is_valid'] ?? false) === true;
        } catch (\Exception $e) {
            Log::error('Token validation failed', ['error' => $e->getMessage()]);
            return false;
        }
    }

    /**
     * Handle deauthorization
     */
    public function deauthorize(string $token): bool
    {
        try {
            $response = $this->client->request('DELETE', 'https://graph.facebook.com/me/permissions', [
                'query' => ['access_token' => $token]
            ]);

            $data = json_decode($response->getBody()->getContents(), true);
            return ($data['success'] ?? false) === true;
        } catch (\Exception $e) {
            Log::error('Deauthorization failed', ['error' => $e->getMessage()]);
            return false;
        }
    }
}