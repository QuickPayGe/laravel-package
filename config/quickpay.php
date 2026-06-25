<?php

return [
    'api_key'        => env('QUICKPAY_API_KEY'),
    'webhook_secret' => env('QUICKPAY_WEBHOOK_SECRET'),
    'test_mode'      => env('QUICKPAY_TEST_MODE', false),
    'site_domain'    => env('QUICKPAY_SITE_DOMAIN', env('APP_URL', '')),
    'base_url'       => env('QUICKPAY_BASE_URL', 'https://api.quickpay.ge/v1'),
    'timeout'        => env('QUICKPAY_TIMEOUT', 30),
];
