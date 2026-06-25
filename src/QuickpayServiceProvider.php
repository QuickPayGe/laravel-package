<?php

declare(strict_types=1);

namespace Quickpay\Laravel;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;
use Quickpay\Config;
use Quickpay\Laravel\Http\Middleware\VerifyQuickpayWebhook;
use Quickpay\Laravel\View\Components\PayButton;
use Quickpay\QuickpayClient;

class QuickpayServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/quickpay.php', 'quickpay');

        $this->app->singleton('quickpay', function ($app) {
            $config = new Config(
                apiKey:     $app['config']['quickpay.api_key'] ?? '',
                baseUrl:    $app['config']['quickpay.base_url'] ?? 'https://api.quickpay.ge/v1',
                timeout:    (int) ($app['config']['quickpay.timeout'] ?? 30),
                siteDomain: $app['config']['quickpay.site_domain'] ?? '',
            );

            return new QuickpayClient($config);
        });

        $this->app->alias('quickpay', QuickpayClient::class);
    }

    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../config/quickpay.php' => config_path('quickpay.php'),
            ], 'quickpay-config');
        }

        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'quickpay');

        Blade::component('quickpay::pay-button', PayButton::class);

        $this->app['router']->aliasMiddleware('quickpay.webhook', VerifyQuickpayWebhook::class);
    }
}
