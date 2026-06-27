<?php

declare(strict_types=1);

namespace App\Events;

use App\Models\Store;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Store Deleted Event
 *
 * Dispatched when a store is deleted.
 * Used for notifications, logging, and cache invalidation.
 */
class StoreDeleted
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     */
    public function __construct(
        public readonly int $storeId,
        public readonly ?int $actorId = null
    ) {}
}
