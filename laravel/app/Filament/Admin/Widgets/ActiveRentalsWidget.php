<?php

namespace App\Filament\Admin\Widgets;

use App\Filament\Admin\Resources\Rentals\RentalResource;
use App\Models\Rental;
use Filament\Actions\Action;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class ActiveRentalsWidget extends BaseWidget
{
    protected int | string | array $columnSpan = 'full';

    protected static ?int $sort = 3;

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Rental::query()
                    ->where(Rental::status, Rental::STATUS_ACTIVE)
                    ->latest()
                    ->limit(10)
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
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('start_date')
                    ->label('Startdatum')
                    ->date('d.m.Y')
                    ->sortable(),

                Tables\Columns\TextColumn::make('end_date')
                    ->label('Enddatum')
                    ->date('d.m.Y')
                    ->sortable()
                    ->color(fn (Rental $record): string =>
                        $record->end_date->isPast() ? 'danger' :
                        ($record->end_date->diffInDays(now()) <= 7 ? 'warning' : 'success')
                    ),

                Tables\Columns\TextColumn::make('fields_count')
                    ->label('Felder')
                    ->counts('fields')
                    ->alignCenter(),

                Tables\Columns\TextColumn::make('total_price')
                    ->label('Gesamtpreis')
                    ->money('EUR', locale: 'de')
                    ->sortable(),

                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->colors([
                        'success' => Rental::STATUS_ACTIVE,
                        'secondary' => Rental::STATUS_COMPLETED,
                        'danger' => Rental::STATUS_CANCELLED,
                    ])
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        Rental::STATUS_ACTIVE => 'Aktiv',
                        Rental::STATUS_COMPLETED => 'Abgeschlossen',
                        Rental::STATUS_CANCELLED => 'Storniert',
                        default => $state,
                    }),
            ])
            ->heading('Aktive Vermietungen')
            ->recordActions([
                Action::make('view')
                    ->label('Ansehen')
                    ->icon('heroicon-m-eye')
                    ->url(fn (Rental $record): string => RentalResource::getViewUrl($record))
            ]);
    }
}
