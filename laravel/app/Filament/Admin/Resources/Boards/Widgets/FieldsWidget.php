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
use Illuminate\Support\Collection;

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

    /**
     * Create Field Action
     */
    public function createFieldAction(): Action
    {
        return Action::make('createField')
            ->label('Neues Feld erstellen')
            ->icon('heroicon-o-plus')
            ->color('success')
            ->form([
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

                // Prüfe ob bereits ein Feld an dieser Position existiert
                $existingField = Field::where(Field::board_id, $this->record->id)
                    ->where(Field::row, $data[Field::row])
                    ->where(Field::column, $data[Field::column])
                    ->first();

                if ($existingField) {
                    Notification::make()
                        ->warning()
                        ->title('Position bereits belegt')
                        ->body('An dieser Position existiert bereits ein Feld.')
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
     * Open create field modal with position
     */
    public function openCreateFieldModal(int $row, int $column): void
    {
        $this->selectedRow = $row;
        $this->selectedColumn = $column;

        $this->mountAction('createField');
    }
}
