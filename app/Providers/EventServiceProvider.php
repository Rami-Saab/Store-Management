<?php

declare(strict_types=1);

namespace App\Providers;

use App\Events\StoreCreated;
use App\Events\StoreDeleted;
use App\Events\StoreUpdated;
use App\Listeners\InvalidateStoreCache;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array<class-string, array<int, class-string>>
     */
    protected $listen = [
        Registered::class => [
            SendEmailVerificationNotification::class,
        ],

        StoreCreated::class => [
            InvalidateStoreCache::class,
        ],

        StoreUpdated::class => [
            InvalidateStoreCache::class,
        ],

        StoreDeleted::class => [
            InvalidateStoreCache::class,
        ],
    ];

    /**
     * Register any events for your application.
     */
    public function boot(): void
    {
        //
    }
}