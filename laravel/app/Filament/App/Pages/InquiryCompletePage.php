<?php

namespace App\Filament\App\Pages;

use App\Models\Inquiry;
use BackedEnum;
use Filament\Pages\Page;
use Filament\Support\Enums\Width;

class InquiryCompletePage extends Page
{
    protected string $view = 'filament.app.pages.inquiry-complete-page';

    protected static string|null|BackedEnum $navigationIcon = 'heroicon-o-check-circle';

    protected static ?string $title = 'Anfrage erfolgreich gesendet';

    // Hide from navigation
    protected static bool $shouldRegisterNavigation = false;

    public ?Inquiry $inquiry = null;

    public function mount(): void
    {
        // Check if session variable exists
        if (!session()->has('inquiry_complete')) {
            $this->redirect(InquiryPage::getUrl());
            return;
        }

        $inquiryId = session('inquiry_complete');

        // Load inquiry with relations
        $this->inquiry = Inquiry::with(['board', 'fields'])
            ->find($inquiryId);

        if (!$this->inquiry) {
            // Clear session and redirect if inquiry not found
            session()->forget('inquiry_complete');
            $this->redirect(InquiryPage::getUrl());
            return;
        }

        // Clear the session variable after loading to prevent page refresh
        session()->forget('inquiry_complete');
    }

    public function getTotalPricePerMonth(): float
    {
        if (!$this->inquiry || !$this->inquiry->fields) {
            return 0;
        }

        return $this->inquiry->fields->sum('price_per_month');
    }

    public function getTotalPrice(): float
    {
        $pricePerMonth = $this->getTotalPricePerMonth();

        if (!$this->inquiry || !$this->inquiry->start_date || !$this->inquiry->end_date) {
            return $pricePerMonth;
        }

        try {
            $startDate = $this->inquiry->start_date;
            $endDate = $this->inquiry->end_date;

            // Calculate number of months (rounded up)
            $months = $startDate->diffInMonths($endDate);
            if ($startDate->copy()->addMonths($months) < $endDate) {
                $months++;
            }

            $months = max(1, $months); // Minimum 1 month

            return $pricePerMonth * $months;
        } catch (\Exception $e) {
            return $pricePerMonth;
        }
    }

    public function getMaxContentWidth(): Width
    {
        return Width::Large;
    }

    /**
     * Navigate back to inquiry page
     */
    public function backToInquiry(): void
    {
        $this->redirect(InquiryPage::getUrl());
    }
}
