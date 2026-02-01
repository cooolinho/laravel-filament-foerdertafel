<?php

namespace App\Filament\Resources\Rentals\Tables;

use App\Models\Customer;
use App\Models\Rental;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class RentalsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('customer.' . Customer::name)
                    ->label('Customer')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('customer.' . Customer::company_name)
                    ->label('Company')
                    ->searchable()
                    ->placeholder('—')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make(Rental::start_date)
                    ->label('Start Date')
                    ->date()
                    ->sortable(),

                TextColumn::make(Rental::end_date)
                    ->label('End Date')
                    ->date()
                    ->placeholder('Ongoing')
                    ->sortable(),

                TextColumn::make(Rental::total_price)
                    ->label('Price/Month')
                    ->money('EUR')
                    ->sortable(),

                TextColumn::make('fields_count')
                    ->label('Fields')
                    ->counts('fields')
                    ->sortable(),

                TextColumn::make(Rental::status)
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        Rental::STATUS_ACTIVE => 'success',
                        Rental::STATUS_COMPLETED => 'gray',
                        Rental::STATUS_CANCELLED => 'danger',
                        default => 'warning',
                    })
                    ->sortable(),

                TextColumn::make(Rental::notes)
                    ->label('Notes')
                    ->limit(50)
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('created_at')
                    ->label('Created At')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make(Rental::status)
                    ->label('Status')
                    ->options([
                        Rental::STATUS_ACTIVE => 'Active',
                        Rental::STATUS_COMPLETED => 'Completed',
                        Rental::STATUS_CANCELLED => 'Cancelled',
                    ]),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort(Rental::start_date, 'desc');
    }
}
