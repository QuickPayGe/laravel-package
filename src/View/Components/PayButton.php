<?php

declare(strict_types=1);

namespace Quickpay\Laravel\View\Components;

use Illuminate\View\Component;
use Illuminate\View\View;

class PayButton extends Component
{
    public string $url;

    public function __construct(
        public string $checkoutLink,
        public string $label     = 'Pay Now',
        public string $color     = '#2563eb',
        public string $textColor = '#ffffff',
        public string $size      = 'md',
        public bool   $fullWidth  = false,
        public string $radius    = '8px',
        public string $target    = '_self',
    ) {
        $this->url = str_starts_with($checkoutLink, 'http')
            ? $checkoutLink
            : "https://qpy.ge/c/{$checkoutLink}";
    }

    public function render(): View
    {
        return view('quickpay::components.pay-button');
    }
}
