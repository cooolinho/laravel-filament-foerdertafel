<?php

namespace App\Filament\Admin\Resources\Fields\Schemas;

use App\Models\Board;
use App\Models\Field;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class FieldInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Field Information')
                    ->schema([
                        TextEntry::make(Field::name)
                            ->label('Field Name'),

                        TextEntry::make('board.' . Board::name)
                            ->label('Board'),

                        TextEntry::make(Field::status)
                            ->label('Status')
                            ->badge()
                            ->color(fn (string $state): string => match ($state) {
                                Field::STATUS_AVAILABLE => 'success',
                                Field::STATUS_RENTED => 'danger',
                                Field::STATUS_RESERVED => 'warning',
                                default => 'gray',
                            }),

                        TextEntry::make(Field::description)
                            ->label('Description')
                            ->placeholder('No description provided')
                            ->columnSpanFull(),
                    ])
                    ->columns(3),

                Section::make('Position & Size')
                    ->schema([
                        TextEntry::make(Field::row)
                            ->label('Row')
                            ->numeric(),

                        TextEntry::make(Field::column)
                            ->label('Column')
                            ->numeric(),

                        TextEntry::make(Field::width)
                            ->label('Width')
                            ->numeric(),

                        TextEntry::make(Field::height)
                            ->label('Height')
                            ->numeric(),
                    ])
                    ->columns(4),

                Section::make('Pricing')
                    ->schema([
                        TextEntry::make(Field::price_per_month)
                            ->label('Price per Month')
                            ->money('EUR'),
                    ])
                    ->columns(1),

                Section::make('Timestamps')
                    ->schema([
                        TextEntry::make('created_at')
                            ->label('Created At')
                            ->dateTime(),

                        TextEntry::make('updated_at')
                            ->label('Updated At')
                            ->dateTime(),
                    ])
                    ->columns(2)
                    ->collapsible(),
            ]);
    }
}
