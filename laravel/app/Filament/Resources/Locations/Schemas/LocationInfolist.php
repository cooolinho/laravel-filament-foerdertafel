<?php

namespace App\Filament\Resources\Locations\Schemas;

use App\Models\Location;
use Filament\Schemas\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class LocationInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Location Information')
                    ->schema([
                        TextEntry::make(Location::name)
                            ->label('Location Name'),

                        TextEntry::make(Location::address)
                            ->label('Address'),

                        TextEntry::make(Location::description)
                            ->label('Description')
                            ->placeholder('No description provided'),
                    ])
                    ->columns(2),

                Section::make('Boards')
                    ->schema([
                        TextEntry::make('boards_count')
                            ->label('Number of Boards')
                            ->state(fn ($record) => $record->boards()->count()),
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
