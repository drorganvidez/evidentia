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
        'token' => env('POSTMARK_TOKEN'),
    ],

    'resend' => [
        'key' => env('RESEND_KEY'),
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

    'us_sso' => [
        'enabled' => (bool) env('US_SSO_ENABLED', false),
        'base_url' => env('US_SSO_BASE_URL', 'https://sso.us.es/cas'),
        'login_path' => env('US_SSO_LOGIN_PATH', '/login'),
        'validate_path' => env('US_SSO_VALIDATE_PATH', '/p3/serviceValidate'),
        'mock_enabled' => (bool) env('US_SSO_MOCK_ENABLED', false),
    ],

];
