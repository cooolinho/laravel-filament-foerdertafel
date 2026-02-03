<?php

namespace App\Filament\Admin\Widgets;

use App\Models\Field;
use Filament\Widgets\ChartWidget;

class FieldStatusWidget extends ChartWidget
{
    protected static ?int $sort = 4;

    public function getHeading(): ?string
    {
        return 'Feldstatus Übersicht';
    }

    protected function getData(): array
    {
        $available = Field::where(Field::status, Field::STATUS_AVAILABLE)->count();
        $rented = Field::where(Field::status, Field::STATUS_RENTED)->count();
        $reserved = Field::where(Field::status, Field::STATUS_RESERVED)->count();

        return [
            'datasets' => [
                [
                    'label' => 'Feldstatus',
                    'data' => [$available, $rented, $reserved],
                    'backgroundColor' => [
                        'rgb(34, 197, 94)', // green for available
                        'rgb(59, 130, 246)', // blue for rented
                        'rgb(251, 146, 60)', // orange for reserved
                    ],
                ],
            ],
            'labels' => ['Verfügbar', 'Vermietet', 'Reserviert'],
        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
    }

    protected function getOptions(): array
    {
        return [
            'plugins' => [
                'legend' => [
                    'display' => true,
                    'position' => 'bottom',
                ],
            ],
        ];
    }
}
