<?php

namespace App\Filament\Resources\Fields\Schemas;

use App\Models\Board;
use App\Models\Field;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class FieldForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Field Information')
                    ->schema([
                        Select::make(Field::board_id)
                            ->label('Board')
                            ->relationship('board', Board::name)
                            ->required()
                            ->searchable()
                            ->preload(),

                        TextInput::make(Field::name)
                            ->label('Field Name')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('z.B. Tor, Strafraum, Mittelkreis'),

                        Select::make(Field::status)
                            ->label('Status')
                            ->options([
                                Field::STATUS_AVAILABLE => 'Available',
                                Field::STATUS_RENTED => 'Rented',
                                Field::STATUS_RESERVED => 'Reserved',
                            ])
                            ->default(Field::STATUS_AVAILABLE)
                            ->required(),
                    ])
                    ->columns(2),

                Section::make('Position & Size')
                    ->schema([
                        TextInput::make(Field::row)
                            ->label('Row')
                            ->required()
                            ->numeric()
                            ->minValue(0),

                        TextInput::make(Field::column)
                            ->label('Column')
                            ->required()
                            ->numeric()
                            ->minValue(0),

                        TextInput::make(Field::width)
                            ->label('Width')
                            ->required()
                            ->numeric()
                            ->minValue(1)
                            ->default(1),

                        TextInput::make(Field::height)
                            ->label('Height')
                            ->required()
                            ->numeric()
                            ->minValue(1)
                            ->default(1),
                    ])
                    ->columns(4),

                Section::make('Pricing')
                    ->schema([
                        TextInput::make(Field::price_per_month)
                            ->label('Price per Month (€)')
                            ->required()
                            ->numeric()
                            ->prefix('€')
                            ->minValue(0)
                            ->step(0.01),
                    ])
                    ->columns(1),

                Section::make('Additional Information')
                    ->schema([
                        Textarea::make(Field::description)
                            ->label('Description')
                            ->rows(4)
                            ->placeholder('Beschreibung des Feldes...'),
                    ])
                    ->columns(1)
                    ->collapsible(),
            ]);
    }
}
