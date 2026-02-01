<?php

namespace App\Filament\Admin\Resources\Boards\Tables;

use App\Models\Board;
use App\Models\Location;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class BoardsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make(Board::name)
                    ->label('Board Name')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('location.' . Location::name)
                    ->label('Location')
                    ->searchable()
                    ->sortable(),

                TextColumn::make(Board::rows)
                    ->label('Rows')
                    ->numeric()
                    ->sortable(),

                TextColumn::make(Board::columns)
                    ->label('Columns')
                    ->numeric()
                    ->sortable(),

                TextColumn::make('fields_count')
                    ->label('Total Fields')
                    ->counts('fields')
                    ->sortable(),

                TextColumn::make(Board::description)
                    ->label('Description')
                    ->limit(50)
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('created_at')
                    ->label('Created At')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('updated_at')
                    ->label('Updated At')
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
