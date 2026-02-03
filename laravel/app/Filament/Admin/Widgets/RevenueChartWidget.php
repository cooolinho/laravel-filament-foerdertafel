<?php

namespace App\Filament\Admin\Widgets;

use App\Models\Rental;
use Filament\Widgets\ChartWidget;

class RevenueChartWidget extends ChartWidget
{
    protected static ?int $sort = 5;

    public ?string $filter = '6months';

    public function getHeading(): ?string
    {
        return 'Umsatzentwicklung';
    }

    protected function getData(): array
    {
        $months = $this->getMonthsCount();
        $data = [];
        $labels = [];

        for ($i = $months - 1; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $monthStart = $date->copy()->startOfMonth();
            $monthEnd = $date->copy()->endOfMonth();

            $revenue = Rental::where(Rental::status, '!=', Rental::STATUS_CANCELLED)
                ->where(function ($query) use ($monthStart, $monthEnd) {
                    $query->whereBetween(Rental::start_date, [$monthStart, $monthEnd])
                        ->orWhereBetween(Rental::end_date, [$monthStart, $monthEnd])
                        ->orWhere(function ($query) use ($monthStart, $monthEnd) {
                            $query->where(Rental::start_date, '<=', $monthStart)
                                ->where(Rental::end_date, '>=', $monthEnd);
                        });
                })
                ->sum(Rental::total_price);

            $data[] = round($revenue, 2);
            $labels[] = $date->format('M Y');
        }

        return [
            'datasets' => [
                [
                    'label' => 'Umsatz (€)',
                    'data' => $data,
                    'fill' => 'start',
                    'backgroundColor' => 'rgba(59, 130, 246, 0.1)',
                    'borderColor' => 'rgb(59, 130, 246)',
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }

    protected function getFilters(): ?array
    {
        return [
            '3months' => '3 Monate',
            '6months' => '6 Monate',
            '12months' => '12 Monate',
        ];
    }

    protected function getMonthsCount(): int
    {
        return match ($this->filter) {
            '3months' => 3,
            '6months' => 6,
            '12months' => 12,
            default => 6,
        };
    }

    protected function getOptions(): array
    {
        return [
            'plugins' => [
                'legend' => [
                    'display' => true,
                ],
            ],
            'scales' => [
                'y' => [
                    'beginAtZero' => true,
                    'ticks' => [
                        'callback' => 'function(value) { return value + " €"; }',
                    ],
                ],
            ],
        ];
    }
}
