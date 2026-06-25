<?php

declare(strict_types=1);

use Illuminate\Http\Request;
use Quickpay\Laravel\Http\Middleware\VerifyQuickpayWebhook;
use Quickpay\Laravel\QuickpayServiceProvider;
use Quickpay\Webhook\WebhookEvent;

function makeSignature(string $secret, int $timestamp, string $payload): string
{
    $hmac = hash_hmac('sha256', "$timestamp.$payload", $secret);
    return "t=$timestamp,v1=$hmac";
}

it('passes a valid webhook signature and binds event on request', function () {
    $secret    = 'whsec_testsecret';
    $payload   = json_encode(['type' => 'payment.paid', 'occurred_at' => '2024-01-01T00:00:00Z', 'payment' => [
        'uuid'       => 'abc',
        'status'     => 'paid',
        'amount'     => 50,
        'currency'   => 'GEL',
        'created_at' => '2024-01-01T00:00:00Z',
    ]]);
    $timestamp = time();
    $sig       = makeSignature($secret, $timestamp, $payload);

    config(['quickpay.webhook_secret' => $secret]);

    $request = Request::create('/webhook', 'POST', [], [], [], [], $payload);
    $request->headers->set('QUICKPAY-SIGNATURE', $sig);
    $request->headers->set('Content-Type', 'application/json');

    $middleware = new VerifyQuickpayWebhook();
    $next       = fn ($req) => response('ok');

    $response = $middleware->handle($request, $next);

    expect($response->getContent())->toBe('ok');
    expect($request->attributes->get('quickpay_event'))->toBeInstanceOf(WebhookEvent::class);
});

it('returns 403 JSON for a wrong signature', function () {
    $payload   = json_encode(['type' => 'payment.paid']);
    $timestamp = time();
    $sig       = "t=$timestamp,v1=badhash";

    config(['quickpay.webhook_secret' => 'real_secret']);

    $request = Request::create('/webhook', 'POST', [], [], [], [], $payload);
    $request->headers->set('QUICKPAY-SIGNATURE', $sig);

    $middleware = new VerifyQuickpayWebhook();
    $next       = fn ($req) => response('ok');

    $response = $middleware->handle($request, $next);

    expect($response->getStatusCode())->toBe(403);
    expect(json_decode($response->getContent(), true))->toHaveKey('error');
});

it('returns 403 JSON when QUICKPAY-SIGNATURE header is missing', function () {
    $request = Request::create('/webhook', 'POST', [], [], [], [], '{}');

    $middleware = new VerifyQuickpayWebhook();
    $next       = fn ($req) => response('ok');

    $response = $middleware->handle($request, $next);

    expect($response->getStatusCode())->toBe(403);
    expect(json_decode($response->getContent(), true))->toHaveKey('error');
});
