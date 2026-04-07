<?php

namespace App\Filament\App\Pages;

use App\Models\Setting;
use BackedEnum;
use Filament\Pages\Page;

class FieldInformationPage extends Page
{
    protected string $view = 'filament.app.pages.field-information-page';

    protected static string|null|BackedEnum $navigationIcon = 'heroicon-o-information-circle';

    protected static ?string $navigationLabel = 'Feldgrößen';

    protected static ?string $title = 'Feldgrößen & Abmessungen';

    protected static ?int $navigationSort = 3;

    /**
     * Gibt die Feldbreite in cm zurück.
     */
    public function getFieldWidth(): float
    {
        return (float) Setting::get(Setting::field_width_cm, 8.9);
    }

    /**
     * Gibt die Feldhöhe in cm zurück.
     */
    public function getFieldHeight(): float
    {
        return (float) Setting::get(Setting::field_height_cm, 5.1);
    }

    /**
     * Gibt den Abstand zwischen den Feldern in cm zurück.
     */
    public function getFieldGap(): float
    {
        return (float) Setting::get(Setting::field_gap_cm, 1.2);
    }

    /**
     * Gibt die maximale Anzahl auswählbarer Zeilen zurück.
     */
    public function getMaxRows(): int
    {
        return Setting::getMaxSelectionRows();
    }

    /**
     * Gibt die maximale Anzahl auswählbarer Spalten zurück.
     */
    public function getMaxCols(): int
    {
        return Setting::getMaxSelectionCols();
    }

    /**
     * Gibt alle gültigen Rechteck-Konfigurationen zurück.
     */
    public function getValidRectangles(): array
    {
        return Setting::getValidRectangles();
    }
}
