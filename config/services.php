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

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'resend' => [
        'key' => env('RESEND_KEY'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    'cloudfront' => [
        // The distribution in front of the media disk. Uploading to S3 is only
        // half of publishing: the CDN keeps serving what it cached until this is
        // invalidated, which is how a repaired video went on playing the old
        // 6-second preview long after origin was correct.
        //
        // Unset simply skips invalidation, so an environment without a CDN
        // deploys normally.
        'distribution_id' => env('CLOUDFRONT_DISTRIBUTION_ID', 'E3ONXFHQD9D5OY'),
    ],

    'google_analytics' => [
        // Falls back to the studio's property so a deploy that forgets the env
        // var still measures. Set GA_MEASUREMENT_ID to point a staging or
        // client environment somewhere else.
        'id' => env('GA_MEASUREMENT_ID', 'G-LHT5WBY4MR'),
    ],

    /*
     * IndexNow (Bing, Yandex, Naver, Seznam — not Google). The key is any
     * 8-128 character hex string; it is not a secret, it is a proof of domain
     * ownership, and the same value must be served at /<key>.txt. Leave empty
     * to disable submission entirely.
     */
    'indexnow' => [
        'key' => env('INDEXNOW_KEY', ''),
    ],

];
