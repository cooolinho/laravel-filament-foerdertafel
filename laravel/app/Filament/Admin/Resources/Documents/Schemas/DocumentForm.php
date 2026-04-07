<?php

namespace App\Filament\Admin\Resources\Documents\Schemas;

use App\Models\Customer;
use App\Models\Document;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\MorphToSelect;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class DocumentForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Dokumentinformationen')
                    ->schema([
                        Select::make(Document::type)
                            ->label('Dokumententyp')
                            ->options(Document::getTypes())
                            ->required()
                            ->native(false),

                        TextInput::make(Document::title)
                            ->label('Titel')
                            ->required()
                            ->maxLength(255)
                            ->columnSpanFull(),

                        TextInput::make(Document::file_name)
                            ->label('Dateiname (URL & Download)')
                            ->maxLength(255)
                            ->placeholder('z.B. agb.pdf oder widerrufsbelehrung.pdf')
                            ->helperText('Wird als URL-Pfad (/documents/dateiname) und als Dateiname beim Download verwendet. Leer lassen um den Namen automatisch aus der Datei zu übernehmen.')
                            ->columnSpanFull(),

                        Textarea::make(Document::description)
                            ->label('Beschreibung')
                            ->rows(3)
                            ->columnSpanFull(),

                        MorphToSelect::make('documentable')
                            ->label('Zugeordnet zu')
                            ->types([
                                MorphToSelect\Type::make(Customer::class)
                                    ->titleAttribute('name'),
                            ])
                            ->searchable()
                            ->preload()
                            ->columnSpanFull(),

                        Toggle::make(Document::is_public)
                            ->label('Öffentlich zugänglich')
                            ->helperText('Öffentliche Dokumente können im Kundenportal angezeigt und heruntergeladen werden (z.B. AGB, Widerrufsbelehrung)')
                            ->default(false)
                            ->inline(false)
                            ->columnSpanFull(),
                    ])
                    ->columns(2),

                Section::make('Datei')
                    ->schema([
                        FileUpload::make(Document::file_path)
                            ->label('Dokument')
                            ->acceptedFileTypes(['application/pdf'])
                            ->maxSize(10240) // 10 MB
                            ->disk('local')
                            ->directory('documents')
                            ->required()
                            ->downloadable()
                            ->openable()
                            ->columnSpanFull()
                            ->helperText('Nur PDF-Dateien, maximal 10 MB'),
                    ]),

                Section::make('Metadaten')
                    ->schema([
                        KeyValue::make(Document::metadata)
                            ->label('Zusätzliche Metadaten')
                            ->keyLabel('Feld')
                            ->valueLabel('Wert')
                            ->addActionLabel('Metadaten hinzufügen')
                            ->columnSpanFull()
                            ->helperText('z.B. Vertragsnummer, Rechnungsnummer, SEPA-Mandats-Referenz'),
                    ])
                    ->collapsible(),

                Hidden::make(Document::uploaded_by)
                    ->default(auth()->id()),
            ]);
    }
}
