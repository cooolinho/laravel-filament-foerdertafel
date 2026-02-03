<?php

namespace App\Filament\Admin\Widgets;

use App\Filament\Admin\Resources\Fields\FieldResource;
use App\Models\Field;
use App\Models\Rental;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;

class InconsistentFieldStatusWidget extends BaseWidget
{
    protected static ?int $sort = 5;

    protected int | string | array $columnSpan = 'full';

    public function getHeading(): ?string
    {
        return 'Felder mit inkonsistentem Status';
    }

    public function getDescription(): ?string
    {
        return 'Felder die einem aktiven Rental zugeordnet sind, aber nicht als "vermietet" markiert wurden.';
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(self::getInconsistentFieldsQuery())
            ->columns([
                TextColumn::make(Field::name)
                    ->label('Feldname')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('board.name')
                    ->label('Board')
                    ->searchable(),

                TextColumn::make(Field::status)
                    ->label('Aktueller Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        Field::STATUS_AVAILABLE => 'success',
                        Field::STATUS_RENTED => 'danger',
                        Field::STATUS_RESERVED => 'warning',
                        default => 'gray',
                    }),

                TextColumn::make('active_rental')
                    ->label('Aktives Rental')
                    ->getStateUsing(function (Field $record): string {
                        $rental = $record->rentals()
                            ->where(Rental::status, Rental::STATUS_ACTIVE)
                            ->where(Rental::start_date, '<=', now())
                            ->where(Rental::end_date, '>=', now())
                            ->first();

                        if ($rental) {
                            return "Rental #{$rental->id} ({$rental->start_date->format('d.m.Y')} - {$rental->end_date->format('d.m.Y')})";
                        }

                        return '-';
                    })
                    ->wrap(),

                TextColumn::make('customer')
                    ->label('Kunde')
                    ->getStateUsing(function (Field $record): string {
                        $rental = $record->rentals()
                            ->where(Rental::status, Rental::STATUS_ACTIVE)
                            ->where(Rental::start_date, '<=', now())
                            ->where(Rental::end_date, '>=', now())
                            ->with('customer')
                            ->first();

                        return $rental?->customer?->name ?? '-';
                    }),
            ])
            ->recordActions([
                Action::make('view')
                    ->label('Ansehen')
                    ->icon('heroicon-o-eye')
                    ->url(fn (Field $record) => FieldResource::getViewUrl($record))
                    ->openUrlInNewTab(),

                // Modal action for selecting status for field
                Action::make('fix')
                    ->label('Status korrigieren')
                    ->icon('heroicon-o-pencil-square')
                    ->schema([
                        Select::make('status')
                            ->label('Neuen Status auswählen')
                            ->options([
                                Field::STATUS_RENTED => 'rented',
                                Field::STATUS_RESERVED => 'reserved',
                            ])
                            ->required(),
                    ])
                    ->action(function (Field $record, array $data): void {
                        $record->status = $data['status'];
                        $record->save();
                    })
                    ->modalWidth('md')
                    ->recordTitleAttribute(Field::name)
                    ->requiresConfirmation(),
            ])
            ->paginated([10, 25, 50]);
    }

    protected static function getInconsistentFieldsQuery(): Builder
    {
        // Felder die einem aktiven Rental im aktuellen Zeitraum zugeordnet sind,
        // aber nicht den Status "rented" haben
        return Field::query()
            ->whereHas('rentals', function (Builder $query) {
                $query->where(Rental::status, Rental::STATUS_ACTIVE)
                    ->where(Rental::start_date, '<=', now())
                    ->where(Rental::end_date, '>=', now());
            })
            ->where(Field::status, '!=', Field::STATUS_RENTED)
            ->with(['board', 'rentals' => function ($query) {
                $query->where(Rental::status, Rental::STATUS_ACTIVE)
                    ->where(Rental::start_date, '<=', now())
                    ->where(Rental::end_date, '>=', now())
                    ->with('customer');
            }]);
    }

    /**
     * Show only when inconsistent fields exist.
     */
    public static function canView(): bool
    {
        return self::getInconsistentFieldsQuery()->exists();
    }
}
