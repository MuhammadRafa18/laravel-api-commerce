<?php

namespace App\Providers;

use App\Handlers\Order\OrderHandler;
use App\Handlers\Order\OrderHandlerInterface;
use App\Models\Category;
use App\Models\Product;
use App\Models\SkinType;
use App\Observers\CategoryObserver;
use App\Observers\ProductObserver;
use App\Observers\SkinTypeObserver;
use Illuminate\Support\ServiceProvider;
use Midtrans\Config as MidtransConfig;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(
            OrderHandlerInterface::class,
            OrderHandler::class
        );
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Product::observe(ProductObserver::class);
        Category::observe(CategoryObserver::class);
        SkinType::observe(SkinTypeObserver::class);
        MidtransConfig::$serverKey = config('Midtrans.server_key');
        MidtransConfig::$clientKey = config('Midtrans.client_key');
        MidtransConfig::$isProduction = config('Midtrans.is_production');
        MidtransConfig::$isSanitized = config('Midtrans.is_sanitized');
        MidtransConfig::$is3ds = config('Midtrans.is_3ds');
    }
}
