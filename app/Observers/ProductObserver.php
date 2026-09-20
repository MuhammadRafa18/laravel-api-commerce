<?php

namespace App\Observers;

use App\Models\Product;
use Illuminate\Support\Facades\Cache;

class ProductObserver
{
    public function clearProductCache(Product $product)
    {

        // Clear cache detail berdasarkan ID
        Cache::forget("product_id_{$product->id}");

        // Clear cache detail berdasarkan slug
        if ($product->slug) {
            Cache::forget("product_slug_{$product->slug}");
        }

        // Clear cache semua halaman product
        for ($page = 1; $page <= 100; $page++) {
            Cache::forget("products_page_{$page}");
        }
    }

    /**
     * Handle the Product "created" event.
     */
    public function created(Product $product): void
    {
        $this->clearProductCache($product);
    }

    /**
     * Handle the Product "updated" event.
     */
    public function updated(Product $product): void
    {
        $this->clearProductCache($product);

        // Kalau slug berubah, hapus cache slug lama juga
        $oldSlug = $product->getOriginal('slug');

        if ($oldSlug && $oldSlug !== $product->slug) {
            Cache::forget("product_slug_{$oldSlug}");
        }
    }

    /**
     * Handle the Product "deleted" event.
     */
    public function deleted(Product $product): void
    {
        $this->clearProductCache($product);
    }
}
