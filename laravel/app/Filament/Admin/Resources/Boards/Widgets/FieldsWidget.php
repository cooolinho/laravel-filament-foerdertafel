<?php

namespace App\Filament\Admin\Resources\Boards\Widgets;

use App\Models\Board;
use App\Models\Field;
use App\Models\Rental;
use Filament\Actions\Action;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Widgets\Widget;

class FieldsWidget extends Widget implements HasForms, HasActions
{
    use InteractsWithForms;
    use InteractsWithActions;

    protected string $view = 'filament.admin.resources.boards.widgets.fields-widget';
    protected int | string | array $columnSpan = 'full';

    public ?Board $record = null;
    public ?int $selectedRow = null;
    public ?int $selectedColumn = null;

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
//                    ->where(Rental::start_date, '<=', now())
//                    ->where(Rental::end_date, '>=', now())
                ;
            }, 'rentals.customer'])
            ->get();

        // Create grid structure with null values
        $grid = [];
        for ($row = 1; $row <= $this->record->{Board::rows}; $row++) {
            $grid[$row] = [];
            for ($col = 1; $col <= $this->record->{Board::columns}; $col++) {
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
                    if (array_key_exists($r, $grid) && array_key_exists($c, $grid[$r])) {
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
//            ->where(Rental::start_date, '<=', now())
//            ->where(Rental::end_date, '>=', now())
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

    /**
     * Create Field Action
     */
    public function createFieldAction(): Action
    {
        return Action::make('createField')
            ->label('Neues Feld erstellen')
            ->icon('heroicon-o-plus')
            ->color('success')
            ->schema([
                TextInput::make(Field::name)
                    ->label('Feldname')
                    ->required()
                    ->maxLength(255)
                    ->placeholder('z.B. Tor, Strafraum, Mittelkreis'),

                TextInput::make(Field::row)
                    ->label('Reihe')
                    ->required()
                    ->numeric()
                    ->minValue(1)
                    ->default(fn () => $this->selectedRow),

                TextInput::make(Field::column)
                    ->label('Spalte')
                    ->required()
                    ->numeric()
                    ->minValue(1)
                    ->default(fn () => $this->selectedColumn),

                TextInput::make(Field::width)
                    ->label('Breite')
                    ->required()
                    ->numeric()
                    ->minValue(1)
                    ->default(1),

                TextInput::make(Field::height)
                    ->label('Höhe')
                    ->required()
                    ->numeric()
                    ->minValue(1)
                    ->default(1),

                TextInput::make(Field::price_per_month)
                    ->label('Preis pro Monat (€)')
                    ->required()
                    ->numeric()
                    ->prefix('€')
                    ->minValue(0)
                    ->step(0.01)
                    ->default(100.00),

                Select::make(Field::status)
                    ->label('Status')
                    ->options([
                        Field::STATUS_AVAILABLE => 'Verfügbar',
                        Field::STATUS_RENTED => 'Vermietet',
                        Field::STATUS_RESERVED => 'Reserviert',
                    ])
                    ->default(Field::STATUS_AVAILABLE)
                    ->required(),

                Textarea::make(Field::description)
                    ->label('Beschreibung')
                    ->rows(3)
                    ->placeholder('Optional: Beschreibung des Feldes...'),
            ])
            ->action(function (array $data) {
                if (!$this->record) {
                    Notification::make()
                        ->danger()
                        ->title('Fehler')
                        ->body('Board nicht gefunden.')
                        ->send();
                    return;
                }

                $newRow = $data[Field::row];
                $newCol = $data[Field::column];
                $newWidth = $data[Field::width];
                $newHeight = $data[Field::height];

                // Prüfe ob das neue Feld innerhalb des Boards liegt
                if ($newRow + $newHeight > $this->record->{Board::rows} + 1 ||
                    $newCol + $newWidth > $this->record->{Board::columns} + 1) {
                    Notification::make()
                        ->warning()
                        ->title('Feld zu groß')
                        ->body('Das Feld passt nicht vollständig auf das Board.')
                        ->send();
                    return;
                }

                // Prüfe ob eine der Positionen bereits belegt ist
                $hasOverlap = Field::where(Field::board_id, $this->record->id)
                    ->get()
                    ->contains(function ($existingField) use ($newRow, $newCol, $newWidth, $newHeight) {
                        $existingRow = $existingField->{Field::row};
                        $existingCol = $existingField->{Field::column};
                        $existingWidth = $existingField->{Field::width};
                        $existingHeight = $existingField->{Field::height};

                        // Prüfe auf Überlappung in beide Richtungen
                        $rowOverlap = $newRow < $existingRow + $existingHeight && $newRow + $newHeight > $existingRow;
                        $colOverlap = $newCol < $existingCol + $existingWidth && $newCol + $newWidth > $existingCol;

                        return $rowOverlap && $colOverlap;
                    });

                if ($hasOverlap) {
                    Notification::make()
                        ->warning()
                        ->title('Position bereits belegt')
                        ->body('Das Feld überschneidet sich mit einem bestehenden Feld.')
                        ->send();
                    return;
                }

                $data[Field::board_id] = $this->record->id;

                Field::create($data);

                Notification::make()
                    ->success()
                    ->title('Feld erstellt')
                    ->body('Das Feld wurde erfolgreich erstellt.')
                    ->send();

                // Reset selection
                $this->selectedRow = null;
                $this->selectedColumn = null;
            });
    }

    /**
     * Fill All Fields Action - Creates fields for all empty positions
     */
    public function fillAllFieldsAction(): Action
    {
        return Action::make('fillAllFields')
            ->label('Alle restlichen Felder erstellen')
            ->icon('heroicon-o-squares-plus')
            ->color('primary')
            ->requiresConfirmation()
            ->modalHeading('Alle restlichen Felder erstellen?')
            ->modalDescription('Möchten Sie für alle leeren Positionen im Raster automatisch Felder erstellen? Bereits vorhandene Felder bleiben unverändert.')
            ->modalSubmitActionLabel('Ja, Felder erstellen')
            ->modalCancelActionLabel('Abbrechen')
            ->schema([
                TextInput::make('defaultPricePerMonth')
                    ->label('Standardpreis pro Monat (€)')
                    ->required()
                    ->numeric()
                    ->prefix('€')
                    ->minValue(0)
                    ->step(0.01)
                    ->default(2.50),
            ])
            ->action(function (array $data) {
                if (!$this->record) {
                    Notification::make()
                        ->danger()
                        ->title('Fehler')
                        ->body('Board nicht gefunden.')
                        ->send();
                    return;
                }

                $board = $this->record;
                $createdCount = 0;
                $skippedCount = 0;
                $defaultPricePerMonth = $data['defaultPricePerMonth'] ?? 2.50;

                // Get all existing fields
                $existingFields = Field::where(Field::board_id, $board->id)->get();

                // Create a map of occupied positions
                $occupiedPositions = [];
                foreach ($existingFields as $field) {
                    for ($r = $field->{Field::row}; $r < $field->{Field::row} + $field->{Field::height}; $r++) {
                        for ($c = $field->{Field::column}; $c < $field->{Field::column} + $field->{Field::width}; $c++) {
                            $occupiedPositions[$r . ',' . $c] = true;
                        }
                    }
                }

                // Iterate through all grid positions
                for ($row = 1; $row <= $board->{Board::rows}; $row++) {
                    for ($col = 1; $col <= $board->{Board::columns}; $col++) {
                        // Check if position is already occupied
                        if (isset($occupiedPositions[$row . ',' . $col])) {
                            $skippedCount++;
                            continue;
                        }

                        // Create new field at this position
                        Field::create([
                            Field::board_id => $board->id,
                            Field::name => "Feld {$row}-{$col}",
                            Field::row => $row,
                            Field::column => $col,
                            Field::width => 1,
                            Field::height => 1,
                            Field::price_per_month => $defaultPricePerMonth,
                            Field::status => Field::STATUS_AVAILABLE,
                            Field::description => "Automatisch erstelltes Feld an Position ({$row},{$col})",
                        ]);

                        $createdCount++;
                    }
                }

                if ($createdCount > 0) {
                    Notification::make()
                        ->success()
                        ->title('Felder erstellt')
                        ->body("{$createdCount} neue Felder wurden erfolgreich erstellt. {$skippedCount} Positionen waren bereits belegt.")
                        ->send();
                } else {
                    Notification::make()
                        ->info()
                        ->title('Keine neuen Felder')
                        ->body('Alle Positionen im Raster sind bereits belegt.')
                        ->send();
                }
            });
    }

    /**
     * Open create field modal with position
     */
    public function openCreateFieldModal(int $row, int $column): void
    {
        $this->selectedRow = $row;
        $this->selectedColumn = $column;

        $this->mountAction('createField');
    }

    /**
     * Show rental details modal
     */
    public function showRentalDetailsAction(): Action
    {
        return Action::make('showRentalDetails')
            ->label('Vermietungsdetails')
            ->modalHeading(fn (array $arguments) => 'Vermietungsdetails - Feld ' . ($arguments['fieldName'] ?? ''))
            ->modalContent(function (array $arguments) {
                $rentalId = $arguments['rentalId'] ?? null;

                if (!$rentalId) {
                    return view('filament.components.empty-state', [
                        'message' => 'Keine Vermietungsdaten verfügbar.'
                    ]);
                }

                $rental = Rental::with(['customer', 'fields'])->find($rentalId);

                if (!$rental) {
                    return view('filament.components.empty-state', [
                        'message' => 'Vermietung nicht gefunden.'
                    ]);
                }

                return view('filament.admin.components.rental-details-modal', [
                    'rental' => $rental,
                ]);
            })
            ->modalWidth(\Filament\Support\Enums\Width::FourExtraLarge)
            ->modalSubmitAction(false)
            ->modalCancelActionLabel('Schließen')
            ->closeModalByClickingAway(true);
    }
}
