<?php

namespace App\Filament\App\Pages;

use App\Models\Field;
use App\Models\Inquiry;
use App\Settings\GeneralSettings;
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
            return 0.0;
        }

        return (float) $this->inquiry->fields->sum('price_per_month');
    }

    /**
     * Basispreis = Mietkosten × Monate + Einrichtungskosten.
     * Bei inklusiver MwSt. = Brutto, bei exklusiver = Netto.
     */
    private function getBasePrice(): float
    {
        $pricePerMonth = $this->getTotalPricePerMonth();
        $months        = max(1, (int) ($this->inquiry?->rental_months ?? 1));
        $setupCost     = (float) app(GeneralSettings::class)->initial_setup_cost;

        return round(($pricePerMonth * $months) + $setupCost, 2);
    }

    public function getVatRate(): float
    {
        return (float) (app(GeneralSettings::class)->invoice_vat_rate ?? 0);
    }

    public function isVatInclusive(): bool
    {
        return app(GeneralSettings::class)->isVatInclusive();
    }

    public function getNetTotal(): float
    {
        $base = $this->getBasePrice();
        $rate = $this->getVatRate();

        if ($rate <= 0) {
            return $base;
        }

        if ($this->isVatInclusive()) {
            return round($base / (1 + $rate / 100), 2);
        }

        return $base;
    }

    public function getVatAmount(): float
    {
        $rate = $this->getVatRate();

        if ($rate <= 0) {
            return 0.0;
        }

        if ($this->isVatInclusive()) {
            return round($this->getBasePrice() - $this->getNetTotal(), 2);
        }

        return round($this->getNetTotal() * $rate / 100, 2);
    }

    public function getGrossTotal(): float
    {
        if ($this->isVatInclusive()) {
            return $this->getBasePrice();
        }

        return round($this->getNetTotal() + $this->getVatAmount(), 2);
    }

    public function getTotalPrice(): float
    {
        return $this->getGrossTotal();
    }

    // ── Formatierungs-Hilfsmethoden ───────────────────────────────────────────

    private function formatMoney(float $value): string
    {
        return number_format($value, 2, ',', '.') . ' €';
    }

    private function formatCm(float $value): string
    {
        return number_format($value, 1, ',', '.') . ' cm';
    }

    public function getFormattedPricePerMonth(): string
    {
        return $this->formatMoney($this->getTotalPricePerMonth());
    }

    public function getFormattedSetupCost(): string
    {
        return $this->formatMoney((float) app(GeneralSettings::class)->initial_setup_cost);
    }

    public function getFormattedNetTotal(): string
    {
        return $this->formatMoney($this->getNetTotal());
    }

    public function getFormattedVatRate(): string
    {
        return number_format($this->getVatRate(), 0, ',', '.') . ' %';
    }

    public function getFormattedVatAmount(): string
    {
        return $this->formatMoney($this->getVatAmount());
    }

    public function getFormattedGrossTotal(): string
    {
        return $this->formatMoney($this->getGrossTotal());
    }

    public function getVatLabel(): string
    {
        $prefix = $this->isVatInclusive() ? 'inkl.' : 'zzgl.';

        return "{$prefix} {$this->getFormattedVatRate()} MwSt.:";
    }

    public function getFormattedFieldPricePerMonth(Field $field): string
    {
        return $this->formatMoney((float) $field->price_per_month);
    }

    public function getFormattedFieldWidthCm(): string
    {
        return $this->formatCm((float) app(GeneralSettings::class)->field_width_cm);
    }

    public function getFormattedFieldHeightCm(): string
    {
        return $this->formatCm((float) app(GeneralSettings::class)->field_height_cm);
    }

    public function getFormattedFieldGapCm(): string
    {
        return $this->formatCm((float) app(GeneralSettings::class)->field_gap_cm);
    }

    /**
     * Berechnet die physischen Abmessungen der gebuchten Felder in cm.
     *
     * @return array{rows: int, cols: int, width_cm: float, height_cm: float, width_fmt: string, height_fmt: string}|null
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

        $widthCm  = app(GeneralSettings::class)->calculatePhysicalWidth($selectionCols);
        $heightCm = app(GeneralSettings::class)->calculatePhysicalHeight($selectionRows);

        return [
            'rows'       => $selectionRows,
            'cols'       => $selectionCols,
            'width_cm'   => $widthCm,
            'height_cm'  => $heightCm,
            'width_fmt'  => $this->formatCm($widthCm),
            'height_fmt' => $this->formatCm($heightCm),
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
