<?php

declare(strict_types=1);

namespace Quickpay\Laravel\Events;

final readonly class LeadSubmitted
{
    public function __construct(public array $lead) {}
}
