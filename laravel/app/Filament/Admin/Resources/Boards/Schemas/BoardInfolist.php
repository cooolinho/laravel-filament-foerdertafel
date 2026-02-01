<?php

namespace App\Filament\Admin\Resources\Boards\Schemas;

use App\Models\Board;
use App\Models\Location;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class BoardInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Board Information')
                    ->schema([
                        TextEntry::make(Board::name)
                            ->label('Board Name'),

                        TextEntry::make('location.' . Location::name)
                            ->label('Location'),

                        TextEntry::make(Board::description)
                            ->label('Description')
                            ->placeholder('No description provided'),
                    ])
                    ->columns(2),

                Section::make('Grid Configuration')
                    ->schema([
                        TextEntry::make(Board::rows)
                            ->label('Rows')
                            ->numeric(),

                        TextEntry::make(Board::columns)
                            ->label('Columns')
                            ->numeric(),

                        TextEntry::make('fields_count')
                            ->label('Total Fields')
                            ->state(fn ($record) => $record->fields()->count()),
                    ])
                    ->columns(3),

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
