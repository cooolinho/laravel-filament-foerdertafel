<?php

namespace App\Filament\Admin\Resources\Locations\Schemas;

use App\Models\Location;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class LocationForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Location Information')
                    ->schema([
                        TextInput::make(Location::name)
                            ->label('Location Name')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('z.B. Hauptstandort Berlin'),

                        Textarea::make(Location::address)
                            ->label('Address')
                            ->required()
                            ->rows(3)
                            ->placeholder('Vollständige Adresse eingeben'),

                        Textarea::make(Location::description)
                            ->label('Description')
                            ->rows(4)
                            ->placeholder('Beschreibung des Standorts...'),
                    ])
                    ->columns(1),
            ]);
    }
}
