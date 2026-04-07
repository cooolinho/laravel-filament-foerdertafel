<?php

namespace App\Filament\App\Pages;

use App\Models\Field;
use App\Models\Inquiry;
use App\Models\Setting;
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
//        session()->forget('inquiry_complete');
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

        if (!$this->inquiry || !$this->inquiry->rental_months) {
            return $pricePerMonth;
        }

        try {
            return $pricePerMonth * $this->inquiry->rental_months;
        } catch (\Exception $e) {
            return $pricePerMonth;
        }
    }

    /**
     * Berechnet die physischen Abmessungen der gebuchten Felder in cm.
     *
     * @return array{rows: int, cols: int, width_cm: float, height_cm: float}|null
     */
    public function getSelectedFieldDimensions(): ?array
    {
        if (!$this->inquiry || $this->inquiry->fields->isEmpty()) {
            return null;
        }

        $fields = $this->inquiry->fields;

        $minRow    = $fields->min(Field::row);
        $minCol    = $fields->min(Field::column);
        $maxRowEnd = $fields->map(fn($f) => $f->{Field::row} + $f->{Field::height} - 1)->max();
        $maxColEnd = $fields->map(fn($f) => $f->{Field::column} + $f->{Field::width} - 1)->max();

        $selectionRows = $maxRowEnd - $minRow + 1;
        $selectionCols = $maxColEnd - $minCol + 1;

        return [
            'rows'      => $selectionRows,
            'cols'      => $selectionCols,
            'width_cm'  => Setting::calculatePhysicalWidth($selectionCols),
            'height_cm' => Setting::calculatePhysicalHeight($selectionRows),
        ];
    }

    public function getMaxContentWidth(): Width
    {
        return Width::FourExtraLarge;
    }

    /**
     * Navigate back to inquiry page
     */
    public function backToInquiry(): void
    {
        $this->redirect(InquiryPage::getUrl());
    }
}
