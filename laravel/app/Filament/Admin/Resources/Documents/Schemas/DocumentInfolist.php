<?php

namespace App\Filament\Admin\Resources\Documents\Schemas;

use App\Filament\Admin\Resources\Customers\CustomerResource;
use App\Models\Document;
use Filament\Infolists\Components\KeyValueEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Storage;

class DocumentInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Dokumentinformationen')
                    ->schema([
                        TextEntry::make(Document::type)
                            ->label('Typ')
                            ->badge()
                            ->formatStateUsing(fn ($record) => $record->getTypeLabel())
                            ->color(fn ($state) => match ($state) {
                                Document::TYPE_CONTRACT => 'success',
                                Document::TYPE_INVOICE => 'warning',
                                Document::TYPE_SEPA_MANDATE => 'info',
                                default => 'gray',
                            }),

                        TextEntry::make(Document::title)
                            ->label('Titel'),

                        TextEntry::make(Document::description)
                            ->label('Beschreibung')
                            ->columnSpanFull(),

                        TextEntry::make('documentable.name')
                            ->label('Zugeordnet zu')
                            ->placeholder('Allgemeines Dokument (keine Zuordnung)')
                            ->url(fn (Document $record) => $record->documentable ? CustomerResource::getViewUrl($record->documentable) : null)
                            ->badge()
                            ->color(fn ($record) => $record->isGeneralDocument() ? 'gray' : 'primary'),
                    ])
                    ->columns(2),

                Section::make('Dateiinformationen')
                    ->schema([
                        TextEntry::make(Document::file_name)
                            ->label('Dateiname'),

                        TextEntry::make(Document::mime_type)
                            ->label('Dateityp')
                            ->badge(),

                        TextEntry::make(Document::file_size)
                            ->label('Dateigröße')
                            ->formatStateUsing(fn ($record) => $record->getFileSizeHuman()),

                        TextEntry::make('created_at')
                            ->label('Hochgeladen am')
                            ->dateTime('d.m.Y H:i'),

                        TextEntry::make('uploadedBy.name')
                            ->label('Hochgeladen von'),

                        TextEntry::make(Document::file_path)
                            ->label('Aktionen')
                            ->formatStateUsing(fn () => 'Herunterladen')
                            ->url(fn ($record) => Storage::disk('public')->url($record->file_path))
                            ->openUrlInNewTab()
                            ->color('primary')
                            ->icon('heroicon-o-arrow-down-tray'),
                    ])
                    ->columns(3),

                Section::make('Versionsinformationen')
                    ->schema([
                        TextEntry::make(Document::version)
                            ->label('Version')
                            ->badge()
                            ->color('info'),

                        TextEntry::make(Document::is_current_version)
                            ->label('Aktuelle Version')
                            ->badge()
                            ->formatStateUsing(fn ($state) => $state ? 'Ja' : 'Nein')
                            ->color(fn ($state) => $state ? 'success' : 'gray'),

                        TextEntry::make('versions_count')
                            ->label('Anzahl Versionen')
                            ->state(fn ($record) => $record->getAllVersions()->count())
                            ->badge(),
                    ])
                    ->columns(3)
                    ->visible(fn ($record) => $record->version > 1 || $record->versions()->exists()),

                Section::make('Metadaten')
                    ->schema([
                        KeyValueEntry::make(Document::metadata)
                            ->label('Zusätzliche Metadaten')
                            ->columnSpanFull(),
                    ])
                    ->visible(fn ($record) => !empty($record->metadata))
                    ->collapsible(),
            ]);
    }
}
