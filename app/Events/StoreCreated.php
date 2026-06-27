<?php

declare(strict_types=1);

namespace App\Events;

use App\Models\Store;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Store Created Event
 *
 * Dispatched when a new store is created.
 * Used for notifications, logging, and cache invalidation.
 */
class StoreCreated
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     */
    public function __construct(
        public readonly Store $store,
        public readonly ?int $actorId = null
    ) {}
}
