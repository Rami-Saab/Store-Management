<?php

declare(strict_types=1);

namespace App\Events;

use App\Models\Store;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Store Updated Event
 *
 * Dispatched when a store is updated.
 * Used for notifications, logging, and cache invalidation.
 */
class StoreUpdated
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     */
    public function __construct(
        public readonly Store $store,
        public readonly array $changes,
        public readonly ?int $actorId = null
    ) {}
}
