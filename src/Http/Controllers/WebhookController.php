<?php

declare(strict_types=1);

namespace Quickpay\Laravel\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Quickpay\Laravel\Events\LeadSubmitted;
use Quickpay\Laravel\Events\PaymentExpired;
use Quickpay\Laravel\Events\PaymentFailed;
use Quickpay\Laravel\Events\PaymentPaid;
use Quickpay\Laravel\Events\PaymentRefunded;
use Quickpay\Webhook\WebhookEvent;

class WebhookController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        /** @var WebhookEvent|null $event */
        $event = $request->attributes->get('quickpay_event');

        if ($event === null) {
            return response()->json(['ok' => true]);
        }

        match ($event->type) {
            'payment.paid'     => event(new PaymentPaid($event->payment)),
            'payment.failed'   => event(new PaymentFailed($event->payment)),
            'payment.expired'  => event(new PaymentExpired($event->payment)),
            'payment.refunded' => event(new PaymentRefunded($event->payment)),
            'lead.submitted'   => event(new LeadSubmitted($event->data)),
            default            => null,
        };

        return response()->json(['ok' => true]);
    }
}
