<?php

namespace App\Filament\App\Pages;

use App\Models\Board;
use App\Models\Field;
use App\Models\Rental;
use BackedEnum;
use Filament\Pages\Page;
use Filament\Support\Enums\Width;
use Illuminate\Support\Facades\Route;

class BoardPage extends Page
{
    protected string $view = 'filament.app.pages.board-page';

    protected static string|null|BackedEnum $navigationIcon = 'heroicon-o-rectangle-group';

    protected static ?string $navigationLabel = 'Board';

    protected static ?int $navigationSort = 1;

    public ?Board $board = null;

    public function mount(): void
    {
        // Lade das erste verfügbare Board (oder spezifisches Board via Parameter)
        $boardId = request()->query('board');

        if ($boardId) {
            $this->board = Board::with([
                'fields.rentals' => function ($query) {
                    $query->where(Rental::status, Rental::STATUS_ACTIVE)
                        ->where(Rental::start_date, '<=', now())
                        ->where(Rental::end_date, '>=', now());
                },
                'fields.rentals.customer'
            ])->find($boardId);
        } else {
            $this->board = Board::with([
                'fields.rentals' => function ($query) {
                    $query->where(Rental::status, Rental::STATUS_ACTIVE)
                        ->where(Rental::start_date, '<=', now())
                        ->where(Rental::end_date, '>=', now());
                },
                'fields.rentals.customer'
            ])->first();
        }
    }

    /**
     * Get all fields organized in a grid structure
     */
    public function getFieldsGrid(): array
    {
        if (!$this->board) {
            return [];
        }

        $fields = $this->board->fields;

        // Create grid structure with null values
        $grid = [];
        for ($row = 1; $row <= $this->board->{Board::rows}; $row++) {
            $grid[$row] = [];
            for ($col = 1; $col <= $this->board->{Board::columns}; $col++) {
                $grid[$row][$col] = null;
            }
        }

        // Place fields in grid, marking occupied positions
        foreach ($fields as $field) {
            $fieldRow = $field->{Field::row};
            $fieldCol = $field->{Field::column};
            $fieldWidth = $field->{Field::width};
            $fieldHeight = $field->{Field::height};

            // Mark the top-left position with the field object
            if (array_key_exists($fieldRow, $grid) && array_key_exists($fieldCol, $grid[$fieldRow])) {
                $grid[$fieldRow][$fieldCol] = $field;
            }

            // Mark all other occupied positions as 'occupied' (string marker)
            for ($r = $fieldRow; $r < $fieldRow + $fieldHeight; $r++) {
                for ($c = $fieldCol; $c < $fieldCol + $fieldWidth; $c++) {
                    // Skip the top-left position (already has the field object)
                    if ($r === $fieldRow && $c === $fieldCol) {
                        continue;
                    }

                    // Mark as occupied if within board bounds
                    if (isset($grid[$r][$c])) {
                        $grid[$r][$c] = 'occupied';
                    }
                }
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
     * Get all available boards for selection
     */
    public function getAvailableBoards(): array
    {
        return Board::all()->pluck('name', 'id')->toArray();
    }

    public function getMaxContentWidth(): Width
    {
        return Width::Full;
    }
}
