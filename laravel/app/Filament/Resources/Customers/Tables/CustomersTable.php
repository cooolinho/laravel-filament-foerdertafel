<?php

namespace App\Filament\Resources\Customers\Tables;

use App\Models\Customer;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class CustomersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make(Customer::name)
                    ->label('Name')
                    ->searchable()
                    ->sortable(),

                TextColumn::make(Customer::company_name)
                    ->label('Company')
                    ->searchable()
                    ->sortable()
                    ->placeholder('—'),

                TextColumn::make(Customer::email)
                    ->label('Email')
                    ->searchable()
                    ->copyable()
                    ->sortable(),

                TextColumn::make(Customer::phone)
                    ->label('Phone')
                    ->searchable()
                    ->placeholder('—'),

                TextColumn::make('rentals_count')
                    ->label('Active Rentals')
                    ->counts('rentals')
                    ->sortable(),

                TextColumn::make(Customer::payment_method)
                    ->label('Payment Method')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('created_at')
                    ->label('Created At')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
