<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Meta (Facebook) Application Settings
    |--------------------------------------------------------------------------
    */

    'app_id' => env('META_APP_ID'),
    'app_secret' => env('META_APP_SECRET'),
    
    /*
    |--------------------------------------------------------------------------
    | API Configuration
    |--------------------------------------------------------------------------
    */
    
    'api_version' => env('META_API_VERSION', 'v18.0'),
    'redirect_url' => env('META_REDIRECT_URL'),
    
    /*
    |--------------------------------------------------------------------------
    | Webhook Configuration
    |--------------------------------------------------------------------------
    */
    
    'webhook_verify_token' => env('META_WEBHOOK_VERIFY_TOKEN'),
    
    /*
    |--------------------------------------------------------------------------
    | Required Permissions
    |--------------------------------------------------------------------------
    */
    
    'permissions' => [
        'email',
        'leads_retrieval',
        'pages_manage_metadata',
        'pages_show_list',
        'pages_manage_ads',
        'business_management',
        'pages_read_engagement',
        'ads_management',
        'ads_read'
    ],
    
    /*
    |--------------------------------------------------------------------------
    | Logging Configuration
    |--------------------------------------------------------------------------
    */
    
    'log_channel' => env('META_LOG_CHANNEL', 'stack'),
];