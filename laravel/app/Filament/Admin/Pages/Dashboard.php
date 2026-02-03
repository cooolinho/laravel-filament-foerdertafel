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

    protected static array $defaultWidgets = [
        StatsOverviewWidget::class,
        RecentInquiriesWidget::class,
        ActiveRentalsWidget::class,
        ExpiringRentalsWidget::class,
        FieldStatusWidget::class,
        InconsistentFieldStatusWidget::class,
        RevenueChartWidget::class,
    ];

    public function getWidgets(): array
    {
        $user = auth()->user();
        $settings = $user?->dashboardSettings;

        // If user has custom settings, use them
        if ($settings) {
            $visibleWidgets = $settings->visible_widgets ?? [];
            $widgetOrder = $settings->widget_order ?? [];

            // If we have a widget order, use it
            if (!empty($widgetOrder)) {
                // Filter by visible widgets and maintain order
                $orderedWidgets = [];
                foreach ($widgetOrder as $widgetClass) {
                    // Only include if it's visible and exists in available widgets
                    if (in_array($widgetClass, $visibleWidgets) && in_array($widgetClass, self::$defaultWidgets)) {
                        $orderedWidgets[] = $widgetClass;
                    }
                }

                // Add any visible widgets that are not in the order (shouldn't happen, but just in case)
                foreach ($visibleWidgets as $widgetClass) {
                    if (in_array($widgetClass, self::$defaultWidgets) && !in_array($widgetClass, $orderedWidgets)) {
                        $orderedWidgets[] = $widgetClass;
                    }
                }

                return $orderedWidgets;
            }

            // If we only have visible widgets (no order), filter by visibility
            if (!empty($visibleWidgets)) {
                return array_filter(self::$defaultWidgets, function ($widget) use ($visibleWidgets) {
                    return in_array($widget, $visibleWidgets);
                });
            }
        }

        // Return all widgets by default
        return self::$defaultWidgets;
    }

    public function getColumns(): int | array
    {
        return 2;
    }
}
