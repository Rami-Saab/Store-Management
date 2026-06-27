<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Events\StoreCreated;
use App\Events\StoreUpdated;
use App\Events\StoreDeleted;
use App\Services\Store\StoreService;

/**
 * Invalidate Store Cache Listener
 *
 * Automatically clears store-related caches when stores are modified.
 */
class InvalidateStoreCache
{
    /**
     * Create a new listener instance.
     */
    public function __construct(
        protected StoreService $storeService
    ) {}

    /**
     * Handle the event.
     */
    public function handle(StoreCreated|StoreUpdated|StoreDeleted $event): void
    {
        $this->storeService->flushStoreCaches();
    }
}
