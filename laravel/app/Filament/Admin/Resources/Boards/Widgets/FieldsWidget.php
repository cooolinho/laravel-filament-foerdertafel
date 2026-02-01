<?php

namespace App\Filament\Admin\Resources\Boards\Widgets;

use App\Models\Board;
use App\Models\Field;
use App\Models\Rental;
use Filament\Widgets\Widget;
use Illuminate\Support\Collection;

class FieldsWidget extends Widget
{
    protected string $view = 'filament.admin.resources.boards.widgets.fields-widget';
    protected int | string | array $columnSpan = 'full';

    public ?Board $record = null;

    /**
     * Get all fields organized in a grid structure
     */
    public function getFieldsGrid(): array
    {
        if (!$this->record) {
            return [];
        }

        // Load all fields with their active rentals
        $fields = $this->record->fields()
            ->with(['rentals' => function ($query) {
                $query->where(Rental::status, Rental::STATUS_ACTIVE)
                    ->where(Rental::start_date, '<=', now())
                    ->where(Rental::end_date, '>=', now());
            }, 'rentals.customer'])
            ->get()
            ->keyBy(function ($field) {
                return $field->{Field::row} . '-' . $field->{Field::column};
            });

        // Create grid structure
        $grid = [];
        for ($row = 1; $row <= $this->record->{Board::rows}; $row++) {
            $grid[$row] = [];
            for ($col = 1; $col <= $this->record->{Board::columns}; $col++) {
                $grid[$row][$col] = $fields->get($row . '-' . $col);
            }
        }

        return $grid;
    }

    /**
     * Get the active rental for a field
     */
    public function getActiveRental(Field $field): ?Rental
    {
        $rental = $field->rentals()
            ->where(Rental::status, Rental::STATUS_ACTIVE)
            ->where(Rental::start_date, '<=', now())
            ->where(Rental::end_date, '>=', now())
            ->with('customer')
            ->first();

        return $rental;
    }

    /**
     * Get status color for a field
     */
    public function getFieldStatusColor(string $status): string
    {
        return match($status) {
            Field::STATUS_AVAILABLE => 'success',
            Field::STATUS_RENTED => 'warning',
            Field::STATUS_RESERVED => 'info',
            default => 'gray'
        };
    }

    /**
     * Get status label for a field
     */
    public function getFieldStatusLabel(string $status): string
    {
        return match($status) {
            Field::STATUS_AVAILABLE => 'Verfügbar',
            Field::STATUS_RENTED => 'Vermietet',
            Field::STATUS_RESERVED => 'Reserviert',
            default => 'Unbekannt'
        };
    }
}
