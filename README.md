# quickpay/laravel

Laravel integration package for the [Quickpay.ge](https://quickpay.ge) payment gateway.

Provides a ServiceProvider, Facade, config publishing, webhook signature middleware, Laravel Events, and a Blade component. Built on top of [quickpay/php-sdk](https://github.com/QuickPayGe/php-sdk).

## Requirements

- PHP 8.2+
- Laravel 11 or 12

## Install

```bash
composer require quickpay/laravel
```

The package is auto-discovered — no manual provider registration needed.

## Setup

### 1. Publish the config

```bash
php artisan vendor:publish --tag=quickpay-config
```

### 2. Add to `.env`

```env
QUICKPAY_API_KEY=qpk_live_your_api_key_here
QUICKPAY_WEBHOOK_SECRET=your_webhook_secret
QUICKPAY_TEST_MODE=false
QUICKPAY_SITE_DOMAIN=https://yourstore.ge
```

## Create a payment and redirect

```php
use Quickpay\Laravel\Facades\Quickpay;

$payment = Quickpay::payments->create([
    'amount'            => 99.99,
    'currency'          => 'GEL',
    'description'       => 'Order #1234',
    'merchant_order_id' => 'order-1234',
    'idempotency_key'   => 'order-1234-attempt-1',
    'customer' => [
        'name'  => 'John Doe',
        'email' => 'john@example.com',
        'phone' => '+995599123456',
    ],
]);

return redirect($payment->paymentUrl);
```

## Webhook routes

### Option A — middleware only (handle manually)

```php
// routes/web.php
Route::post('/webhook/quickpay', function (Request $request) {
    $event = $request->attributes->get('quickpay_event'); // \Quickpay\Webhook\WebhookEvent

    if ($event->type === 'payment.paid') {
        // mark order as paid
    }

    return response()->json(['ok' => true]);
})->middleware('quickpay.webhook');
```

### Option B — auto-dispatch Laravel events

```php
// routes/web.php
use Quickpay\Laravel\Http\Controllers\WebhookController;

Route::post('/webhook/quickpay', WebhookController::class)
    ->middleware('quickpay.webhook');
```

## Listen for events

```php
// app/Providers/EventServiceProvider.php
protected $listen = [
    \Quickpay\Laravel\Events\PaymentPaid::class     => [App\Listeners\HandlePaymentPaid::class],
    \Quickpay\Laravel\Events\PaymentFailed::class   => [App\Listeners\HandlePaymentFailed::class],
    \Quickpay\Laravel\Events\PaymentExpired::class  => [App\Listeners\HandlePaymentExpired::class],
    \Quickpay\Laravel\Events\PaymentRefunded::class => [App\Listeners\HandlePaymentRefunded::class],
    \Quickpay\Laravel\Events\LeadSubmitted::class   => [App\Listeners\HandleLeadSubmitted::class],
];
```

```php
// app/Listeners/HandlePaymentPaid.php
use Quickpay\Laravel\Events\PaymentPaid;

class HandlePaymentPaid
{
    public function handle(PaymentPaid $event): void
    {
        $payment = $event->payment; // \Quickpay\DTO\Payment
        // Update your order, send confirmation email, etc.
    }
}
```

## Blade component

```blade
{{-- Basic usage --}}
<x-quickpay-button :checkout-link="$link->uuid" />

{{-- With options --}}
<x-quickpay-button
    :checkout-link="$link->uuid"
    label="გადახდა"
    color="#10b981"
    text-color="#ffffff"
    size="lg"
    :full-width="true"
    target="_blank"
/>
```

**Props:**

| Prop | Type | Default | Description |
|---|---|---|---|
| `checkout-link` | string | required | Checkout link UUID or full URL |
| `label` | string | `Pay Now` | Button text |
| `color` | string | `#2563eb` | Background color |
| `text-color` | string | `#ffffff` | Text color |
| `size` | string | `md` | `sm` / `md` / `lg` |
| `full-width` | bool | `false` | Stretch to container width |
| `radius` | string | `8px` | Border radius |
| `target` | string | `_self` | Link target (`_blank` etc.) |

## Error handling

```php
use Quickpay\Exceptions\AuthException;
use Quickpay\Exceptions\ValidationException;
use Quickpay\Exceptions\RateLimitException;
use Quickpay\Exceptions\NotFoundException;
use Quickpay\Exceptions\QuickpayException;

try {
    $payment = Quickpay::payments->get('some-uuid');
} catch (AuthException $e) {
    // 401/403
} catch (ValidationException $e) {
    // 422 — field errors in $e->errors
} catch (RateLimitException $e) {
    // 429 — retry after $e->retryAfter() seconds
} catch (NotFoundException $e) {
    // 404
} catch (QuickpayException $e) {
    // catch-all
}
```

## Test mode

Set `QUICKPAY_TEST_MODE=true` and use a `qpk_test_...` API key. Test keys only work in test mode and never reach real gateway APIs.
