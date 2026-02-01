<?php

namespace App\Filament\Resources\Boards\Schemas;

use App\Models\Board;
use App\Models\Location;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class BoardForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Board Information')
                    ->schema([
                        Select::make(Board::location_id)
                            ->label('Location')
                            ->relationship('location', Location::name)
                            ->required()
                            ->searchable()
                            ->preload()
                            ->createOptionForm([
                                TextInput::make(Location::name)
                                    ->label('Name')
                                    ->required()
                                    ->maxLength(255),
                                Textarea::make(Location::address)
                                    ->label('Address')
                                    ->required()
                                    ->rows(3),
                                Textarea::make(Location::description)
                                    ->label('Description')
                                    ->rows(3),
                            ]),

                        TextInput::make(Board::name)
                            ->label('Board Name')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('z.B. Fußballfeld Hauptplatz'),
                    ])
                    ->columns(1),

                Section::make('Grid Configuration')
                    ->schema([
                        TextInput::make(Board::rows)
                            ->label('Number of Rows')
                            ->required()
                            ->numeric()
                            ->minValue(1)
                            ->maxValue(100)
                            ->default(10),

                        TextInput::make(Board::columns)
                            ->label('Number of Columns')
                            ->required()
                            ->numeric()
                            ->minValue(1)
                            ->maxValue(100)
                            ->default(10),
                    ])
                    ->columns(2),

                Section::make('Additional Information')
                    ->schema([
                        Textarea::make(Board::description)
                            ->label('Description')
                            ->rows(4)
                            ->placeholder('Beschreiben Sie das Board...'),
                    ])
                    ->columns(1)
                    ->collapsible(),
            ]);
    }
}
