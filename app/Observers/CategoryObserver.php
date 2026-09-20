<?php

namespace App\Observers;

use App\Models\Category;
use Illuminate\Support\Facades\Cache;

class CategoryObserver
{
    /**
     * Clear all cached category listing pages.
     */
    public function clearCategoryCache(Category $category): void
    {
        for ($page = 1; $page <= 100; $page++) {
            Cache::forget("categories_page_{$page}");
        }
    }

    /**
     * Handle the Category "created" event.
     */
    public function created(Category $category): void
    {
        $this->clearCategoryCache($category);
    }

    /**
     * Handle the Category "updated" event.
     */
    public function updated(Category $category): void
    {
        $this->clearCategoryCache($category);
    }

    /**
     * Handle the Category "deleted" event.
     */
    public function deleted(Category $category): void
    {
        $this->clearCategoryCache($category);
    }
}
