<?php

namespace App\Filament\Admin\Widgets;

use App\Models\Rental;
use Filament\Actions\Action;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class ExpiringRentalsWidget extends BaseWidget
{
    protected int | string | array $columnSpan = 'full';

    protected static ?int $sort = 6;

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Rental::query()
                    ->where(Rental::status, Rental::STATUS_ACTIVE)
                    ->where(Rental::end_date, '>=', now())
                    ->where(Rental::end_date, '<=', now()->addDays(30))
                    ->orderBy(Rental::end_date, 'asc')
            )
            ->columns([
                Tables\Columns\TextColumn::make('customer.name')
                    ->label('Kunde')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('customer.email')
                    ->label('E-Mail')
                    ->searchable()
                    ->copyable()
                    ->icon('heroicon-m-envelope'),

                Tables\Columns\TextColumn::make('customer.phone')
                    ->label('Telefon')
                    ->searchable()
                    ->icon('heroicon-m-phone')
                    ->toggleable(),

                Tables\Columns\TextColumn::make('end_date')
                    ->label('Enddatum')
                    ->date('d.m.Y')
                    ->sortable()
                    ->badge()
                    ->color(fn (Rental $record): string => match (true) {
                        $record->end_date->diffInDays(now()) <= 7 => 'danger',
                        $record->end_date->diffInDays(now()) <= 14 => 'warning',
                        default => 'primary',
                    })
                    ->formatStateUsing(fn ($state, Rental $record): string =>
                        $state->format('d.m.Y') . ' (' . $record->end_date->diffInDays(now()) . ' Tage)'
                    ),

                Tables\Columns\TextColumn::make('fields_count')
                    ->label('Felder')
                    ->counts('fields')
                    ->alignCenter(),

                Tables\Columns\TextColumn::make('total_price')
                    ->label('Gesamtpreis')
                    ->money('EUR', locale: 'de')
                    ->sortable(),
            ])
            ->heading('Bald ablaufende Vermietungen (nächste 30 Tage)')
            ->recordActions([
                Action::make('extend')
                    ->label('Verlängern')
                    ->icon('heroicon-m-arrow-path')
                    ->color('success')
                    ->url(fn (Rental $record): string => route('filament.admin.resources.rentals.edit', ['record' => $record])),

                Action::make('view')
                    ->label('Ansehen')
                    ->icon('heroicon-m-eye')
                    ->url(fn (Rental $record): string => route('filament.admin.resources.rentals.view', ['record' => $record]))
            ])
            ->emptyStateHeading('Keine bald ablaufenden Vermietungen')
            ->emptyStateDescription('Alle Vermietungen laufen noch länger als 30 Tage.')
            ->emptyStateIcon('heroicon-o-check-circle');
    }
}
