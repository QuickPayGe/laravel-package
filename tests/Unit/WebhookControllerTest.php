<?php

declare(strict_types=1);

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Event;
use Quickpay\DTO\Payment;
use Quickpay\Laravel\Events\LeadSubmitted;
use Quickpay\Laravel\Events\PaymentFailed;
use Quickpay\Laravel\Events\PaymentPaid;
use Quickpay\Laravel\Http\Controllers\WebhookController;
use Quickpay\Laravel\QuickpayServiceProvider;
use Quickpay\Webhook\WebhookEvent;



function makePayment(): Payment
{
    return Payment::fromArray([
        'uuid'            => 'pay-uuid-001',
        'status'          => 'paid',
        'amount'          => 99.99,
        'currency'        => 'GEL',
        'payment_url'     => 'https://qpy.ge/pay/pay-uuid-001',
        'merchant_order_id' => 'order-001',
        'description'     => 'Test payment',
        'created_at'      => '2024-01-01T00:00:00Z',
        'updated_at'      => '2024-01-01T00:00:00Z',
    ]);
}

it('dispatches PaymentPaid event for payment.paid webhook type', function () {
    Event::fake();

    $payment = makePayment();
    $event   = new WebhookEvent('payment.paid', [], $payment, new \DateTimeImmutable());

    $request = Request::create('/webhook', 'POST');
    $request->attributes->set('quickpay_event', $event);

    $controller = new WebhookController();
    $response   = $controller($request);

    Event::assertDispatched(PaymentPaid::class, fn ($e) => $e->payment === $payment);
    expect($response->getStatusCode())->toBe(200);
    expect(json_decode($response->getContent(), true))->toBe(['ok' => true]);
});

it('dispatches PaymentFailed event for payment.failed webhook type', function () {
    Event::fake();

    $payment = makePayment();
    $event   = new WebhookEvent('payment.failed', [], $payment, new \DateTimeImmutable());

    $request = Request::create('/webhook', 'POST');
    $request->attributes->set('quickpay_event', $event);

    $controller = new WebhookController();
    $controller($request);

    Event::assertDispatched(PaymentFailed::class);
});

it('dispatches LeadSubmitted with raw array for lead.submitted webhook type', function () {
    Event::fake();

    $data  = ['type' => 'lead.submitted', 'lead' => ['name' => 'John', 'email' => 'john@example.com']];
    $event = new WebhookEvent('lead.submitted', $data, null, new \DateTimeImmutable());

    $request = Request::create('/webhook', 'POST');
    $request->attributes->set('quickpay_event', $event);

    $controller = new WebhookController();
    $controller($request);

    Event::assertDispatched(LeadSubmitted::class, fn ($e) => $e->lead === $data);
});

it('returns 200 without exception for unknown webhook types', function () {
    Event::fake();

    $event = new WebhookEvent('unknown.type', [], null, new \DateTimeImmutable());

    $request = Request::create('/webhook', 'POST');
    $request->attributes->set('quickpay_event', $event);

    $controller = new WebhookController();
    $response   = $controller($request);

    expect($response->getStatusCode())->toBe(200);
    Event::assertNothingDispatched();
});
