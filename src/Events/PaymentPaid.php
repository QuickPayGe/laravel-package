<?php

declare(strict_types=1);

namespace Quickpay\Laravel\Events;

use Quickpay\DTO\Payment;

final readonly class PaymentPaid
{
    public function __construct(public Payment $payment) {}
}
