<?php

namespace App\Filament\Admin\Pages;

use App\Filament\Admin\Widgets\ActiveRentalsWidget;
use App\Filament\Admin\Widgets\ExpiringRentalsWidget;
use App\Filament\Admin\Widgets\FieldStatusWidget;
use App\Filament\Admin\Widgets\InconsistentFieldStatusWidget;
use App\Filament\Admin\Widgets\RecentInquiriesWidget;
use App\Filament\Admin\Widgets\RevenueChartWidget;
use App\Filament\Admin\Widgets\StatsOverviewWidget;
use Filament\Pages\Dashboard as BaseDashboard;

class Dashboard extends BaseDashboard
{
    protected static ?string $navigationLabel = 'Dashboard';

    public function getWidgets(): array
    {
        return [
            StatsOverviewWidget::class,
            RecentInquiriesWidget::class,
            ActiveRentalsWidget::class,
            ExpiringRentalsWidget::class,
            FieldStatusWidget::class,
            InconsistentFieldStatusWidget::class,
            RevenueChartWidget::class,
        ];
    }

    public function getColumns(): int | array
    {
        return 2;
    }
}
