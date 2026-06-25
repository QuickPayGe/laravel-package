<?php

declare(strict_types=1);

namespace Quickpay\Laravel\Tests;

use Orchestra\Testbench\TestCase as BaseTestCase;
use Quickpay\Laravel\QuickpayServiceProvider;

class TestCase extends BaseTestCase
{
    protected function getPackageProviders($app): array
    {
        return [QuickpayServiceProvider::class];
    }
}
