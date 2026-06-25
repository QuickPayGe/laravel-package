<?php

declare(strict_types=1);

namespace Quickpay\Laravel\Facades;

use Illuminate\Support\Facades\Facade;
use Quickpay\Resources\CheckoutLinks;
use Quickpay\Resources\Gateways;
use Quickpay\Resources\Payments;
use Quickpay\Resources\Products;

/**
 * @method static Payments      payments()
 * @method static CheckoutLinks checkoutLinks()
 * @method static Products      products()
 * @method static Gateways      gateways()
 *
 * @see \Quickpay\QuickpayClient
 */
class Quickpay extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'quickpay';
    }
}
