<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Resend, Postmark, AWS, and more. This file provides the de facto
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

    // the food lookup on the recipe form (see FoodLookupServiceProvider).
    // driver: "open_food_facts" (the real API) or "local" (built-in list, no internet needed)
    'food_lookup' => [
        'driver' => env('FOOD_LOOKUP_DRIVER', 'open_food_facts'),
        'open_food_facts_url' => env('OPEN_FOOD_FACTS_URL', 'https://world.openfoodfacts.org'),
        'user_agent' => 'MacroMeals/1.0 (Swansea University student coursework project)',
        'timeout' => 8, // seconds before giving up
        'cache_minutes' => 1440, // remember search results for a day
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

];
