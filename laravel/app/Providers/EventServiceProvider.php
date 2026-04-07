<?php

namespace App\Providers;

use App\Events\InquiryCreated;
use App\Events\InquiryRejected;
use App\Events\RentalCreated;
use App\Events\RentalEnded;
use App\Events\RentalPaid;
use App\Listeners\ReleaseReservedFields;
use App\Listeners\ReserveFieldsForInquiry;
use App\Listeners\SetFieldsToAvailable;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array<class-string, array<int, class-string>>
     */
    protected $listen = [
        InquiryCreated::class => [
            ReserveFieldsForInquiry::class,
//            SendInquiryConfirmationEmail::class,
        ],
        InquiryRejected::class => [
            ReleaseReservedFields::class,
        ],
        RentalCreated::class => [
//            SendRentalConfirmationEmail::class,
        ],
        RentalPaid::class => [
//            SendAccessCodeEmail::class,
        ],
        RentalEnded::class => [
            SetFieldsToAvailable::class,
        ],
    ];

    /**
     * Register any events for your application.
     */
    public function boot(): void
    {
        //
    }

    /**
     * Determine if events and listeners should be automatically discovered.
     */
    public function shouldDiscoverEvents(): bool
    {
        return false;
    }
}
