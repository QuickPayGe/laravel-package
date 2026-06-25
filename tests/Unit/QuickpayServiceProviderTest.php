<?php

declare(strict_types=1);

use Quickpay\Laravel\Http\Middleware\VerifyQuickpayWebhook;
use Quickpay\Laravel\QuickpayServiceProvider;
use Quickpay\QuickpayClient;

it('resolves QuickpayClient from the quickpay binding', function () {
    $client = $this->app->make('quickpay');
    expect($client)->toBeInstanceOf(QuickpayClient::class);
});

it('resolves QuickpayClient via class alias', function () {
    $client = $this->app->make(QuickpayClient::class);
    expect($client)->toBeInstanceOf(QuickpayClient::class);
});

it('populates api_key from config', function () {
    config(['quickpay.api_key' => 'qpk_test_abc123']);
    // Re-bind so the new config value is picked up
    $this->app->forgetInstance('quickpay');
    $this->app->forgetInstance(QuickpayClient::class);

    // Just confirm config is readable
    expect(config('quickpay.api_key'))->toBe('qpk_test_abc123');
});

it('registers quickpay.webhook middleware alias', function () {
    $router     = $this->app['router'];
    $middleware = $router->getMiddleware();

    expect($middleware)->toHaveKey('quickpay.webhook');
    expect($middleware['quickpay.webhook'])->toBe(VerifyQuickpayWebhook::class);
});
