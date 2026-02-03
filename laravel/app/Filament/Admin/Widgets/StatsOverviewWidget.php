<?php

namespace App\Filament\Admin\Widgets;

use App\Models\Customer;
use App\Models\Field;
use App\Models\Inquiry;
use App\Models\Rental;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverviewWidget extends BaseWidget
{
    protected function getStats(): array
    {
        $totalFields = Field::count();
        $rentedFields = Field::where(Field::status, Field::STATUS_RENTED)->count();
        $availableFields = Field::where(Field::status, Field::STATUS_AVAILABLE)->count();
        $reservedFields = Field::where(Field::status, Field::STATUS_RESERVED)->count();

        $activeRentals = Rental::where(Rental::status, Rental::STATUS_ACTIVE)->count();
        $pendingInquiries = Inquiry::where(Inquiry::status, Inquiry::STATUS_PENDING)->count();

        $monthlyRevenue = Rental::where(Rental::status, Rental::STATUS_ACTIVE)
            ->whereMonth(Rental::start_date, now()->month)
            ->whereYear(Rental::start_date, now()->year)
            ->sum(Rental::total_price);

        $totalCustomers = Customer::count();

        return [
            Stat::make('Verfügbare Felder', $availableFields)
                ->description($totalFields . ' Felder gesamt')
                ->descriptionIcon('heroicon-m-square-3-stack-3d')
                ->color('success')
                ->chart([$availableFields, $rentedFields, $reservedFields]),

            Stat::make('Vermietete Felder', $rentedFields)
                ->description(number_format(($rentedFields / max($totalFields, 1)) * 100, 1) . '% Auslastung')
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('primary'),

            Stat::make('Aktive Vermietungen', $activeRentals)
                ->description('Laufende Verträge')
                ->descriptionIcon('heroicon-m-document-text')
                ->color('info'),

            Stat::make('Offene Anfragen', $pendingInquiries)
                ->description('Warten auf Bearbeitung')
                ->descriptionIcon('heroicon-m-inbox')
                ->color($pendingInquiries > 0 ? 'warning' : 'success'),

            Stat::make('Umsatz (Monat)', number_format($monthlyRevenue, 2, ',', '.') . ' €')
                ->description('Aktueller Monat')
                ->descriptionIcon('heroicon-m-currency-euro')
                ->color('success'),

            Stat::make('Kunden', $totalCustomers)
                ->description('Registrierte Kunden')
                ->descriptionIcon('heroicon-m-user-group')
                ->color('primary'),
        ];
    }
}
