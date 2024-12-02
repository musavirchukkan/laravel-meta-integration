<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Meta API Configuration
    |--------------------------------------------------------------------------
    */

    'client_id' => env('META_CLIENT_ID'),
    'client_secret' => env('META_CLIENT_SECRET'),
    'redirect_url' => env('META_REDIRECT_URL'),
    'app_token' => env('META_APP_TOKEN'),
    
    'graph_url' => env('META_GRAPH_URL', 'https://graph.facebook.com/'),
    'graph_version' => env('META_GRAPH_VERSION', 'v18.0'),
    
    'cache' => [
        'enabled' => true,
        'ttl' => 3600, // 1 hour
    ],
    
    'rate_limit' => [
        'max_attempts' => 200,  // Per hour
        'decay_minutes' => 60
    ]
];