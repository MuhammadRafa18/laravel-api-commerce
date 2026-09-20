<?php

namespace App\Observers;

use App\Models\SkinType;
use Illuminate\Support\Facades\Cache;

class SkinTypeObserver
{
    /**
     * Clear all cached skin type listing pages.
     */
    public function clearSkinTypeCache(SkinType $skinType): void
    {
        for ($page = 1; $page <= 100; $page++) {
            Cache::forget("skin_types_page_{$page}");
        }
    }

    /**
     * Handle the SkinType "created" event.
     */
    public function created(SkinType $skinType): void
    {
        $this->clearSkinTypeCache($skinType);
    }

    /**
     * Handle the SkinType "updated" event.
     */
    public function updated(SkinType $skinType): void
    {
        $this->clearSkinTypeCache($skinType);
    }

    /**
     * Handle the SkinType "deleted" event.
     */
    public function deleted(SkinType $skinType): void
    {
        $this->clearSkinTypeCache($skinType);
    }
}
