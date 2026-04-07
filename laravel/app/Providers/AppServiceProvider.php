<?php

namespace App\Providers;

use App\Models\Document;
use App\Models\Inquiry;
use App\Models\Rental;
use App\Observers\DocumentObserver;
use App\Observers\InquiryObserver;
use App\Observers\RentalObserver;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Document::observe(DocumentObserver::class);
        Rental::observe(RentalObserver::class);
        Inquiry::observe(InquiryObserver::class);
    }
}
