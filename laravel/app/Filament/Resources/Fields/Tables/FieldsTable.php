<?php

namespace App\Filament\Resources\Fields\Tables;

use App\Models\Board;
use App\Models\Field;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class FieldsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make(Field::name)
                    ->label('Field Name')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('board.' . Board::name)
                    ->label('Board')
                    ->searchable()
                    ->sortable(),

                TextColumn::make(Field::row)
                    ->label('Row')
                    ->numeric()
                    ->sortable(),

                TextColumn::make(Field::column)
                    ->label('Column')
                    ->numeric()
                    ->sortable(),

                TextColumn::make(Field::width)
                    ->label('Width')
                    ->numeric()
                    ->sortable(),

                TextColumn::make(Field::height)
                    ->label('Height')
                    ->numeric()
                    ->sortable(),

                TextColumn::make(Field::price_per_month)
                    ->label('Price/Month')
                    ->money('EUR')
                    ->sortable(),

                TextColumn::make(Field::status)
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        Field::STATUS_AVAILABLE => 'success',
                        Field::STATUS_RENTED => 'danger',
                        Field::STATUS_RESERVED => 'warning',
                        default => 'gray',
                    })
                    ->sortable(),

                TextColumn::make(Field::description)
                    ->label('Description')
                    ->limit(50)
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('created_at')
                    ->label('Created At')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make(Field::status)
                    ->label('Status')
                    ->options([
                        Field::STATUS_AVAILABLE => 'Available',
                        Field::STATUS_RENTED => 'Rented',
                        Field::STATUS_RESERVED => 'Reserved',
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
            ]);
    }
}
