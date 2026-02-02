<?php

namespace App\Filament\Admin\Resources\Boards\Schemas;

use App\Models\Board;
use App\Models\Location;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
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

                Section::make('Background & Positioning')
                    ->description('Laden Sie ein Hintergrundbild hoch (z.B. Luftaufnahme des Stadions) und positionieren Sie das Raster passend zum Spielfeld.')
                    ->schema([
                        FileUpload::make(Board::background_image)
                            ->label('Hintergrundbild')
                            ->image()
                            ->imageEditor()
                            ->imageEditorAspectRatioOptions([
                                null,
                                '16:9',
                                '4:3',
                                '1:1',
                            ])
                            ->maxSize(5120) // 5MB
                            ->directory('board-backgrounds')
                            ->disk('public')
                            ->downloadable()
                            ->helperText('Empfohlen: Luftaufnahme des Stadions (max. 5MB, JPG/PNG)')
                            ->columnSpanFull(),

                        TextInput::make(Board::grid_offset_x)
                            ->label('Grid Offset X (Pixel)')
                            ->helperText('Horizontaler Versatz des Rasters vom linken Bildrand')
                            ->numeric()
                            ->default(0)
                            ->suffix('px'),

                        TextInput::make(Board::grid_offset_y)
                            ->label('Grid Offset Y (Pixel)')
                            ->helperText('Vertikaler Versatz des Rasters vom oberen Bildrand')
                            ->numeric()
                            ->default(0)
                            ->suffix('px'),

                        TextInput::make(Board::grid_gap)
                            ->label('Grid Gap (Pixel)')
                            ->helperText('Abstand zwischen den Feldern im Raster')
                            ->numeric()
                            ->default(12)
                            ->minValue(0)
                            ->maxValue(50)
                            ->suffix('px'),
                    ])
                    ->columns(3)
                    ->collapsible(),

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
