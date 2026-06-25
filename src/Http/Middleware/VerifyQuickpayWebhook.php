<?php

declare(strict_types=1);

namespace Quickpay\Laravel\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Quickpay\Exceptions\QuickpayException;
use Quickpay\Webhook\WebhookVerifier;
use Symfony\Component\HttpFoundation\Response;

class VerifyQuickpayWebhook
{
    public function __construct(private readonly WebhookVerifier $verifier = new WebhookVerifier()) {}

    public function handle(Request $request, Closure $next): Response
    {
        $rawBody  = $request->getContent();
        $sigHeader = $request->header('QUICKPAY-SIGNATURE', '');

        if ($sigHeader === '') {
            return response()->json(['error' => 'Missing signature'], 403);
        }

        try {
            $event = $this->verifier->verify(
                config('quickpay.webhook_secret', ''),
                $rawBody,
                $sigHeader,
            );
        } catch (QuickpayException) {
            return response()->json(['error' => 'Invalid signature'], 403);
        }

        $request->attributes->set('quickpay_event', $event);

        return $next($request);
    }
}
