<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'key' => env('POSTMARK_API_KEY'),
    ],

    'resend' => [
        'key' => env('RESEND_API_KEY'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],
    
    'fyers' => [
        'app_id' => env('FYERS_APP_ID'),
        'secret_id' => env('FYERS_SECRET_ID'),
        'redirect_uri' => env('FYERS_REDIRECT_URI'),
    ],

    'upstox' => [
        'environment' => env('UPSTOX_ENV', 'sandbox'),
        'client_id' => env('UPSTOX_CLIENT_ID'),
        'client_secret' => env('UPSTOX_CLIENT_SECRET'),
        'redirect_uri' => env('UPSTOX_REDIRECT_URI'),
        'sandbox_url' => env(
            'UPSTOX_SANDBOX_URL',
            'https://sandbox.upstox.com/v2'
        ),
        'live_url' => env(
            'UPSTOX_LIVE_URL',
            'https://api.upstox.com/v2'
        ),
        'sandbox_access_token' => env('UPSTOX_SANDBOX_ACCESS_TOKEN'),
    ],

];
