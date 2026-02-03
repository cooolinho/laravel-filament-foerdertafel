<?php

namespace App\Filament\Admin\Resources\Emails\Schemas;

use App\Filament\Admin\Resources\Customers\CustomerResource;
use App\Filament\Admin\Resources\Documents\DocumentResource;
use App\Models\Document;
use App\Models\Email;
use Filament\Infolists\Components\KeyValueEntry;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Enums\TextSize;
use Illuminate\Support\Facades\Storage;

class EmailInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('E-Mail-Informationen')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextEntry::make(Email::direction)
                                    ->label('Richtung')
                                    ->badge()
                                    ->color(fn (string $state): string => match ($state) {
                                        Email::DIRECTION_INBOUND => 'info',
                                        Email::DIRECTION_OUTBOUND => 'success',
                                        default => 'gray',
                                    })
                                    ->formatStateUsing(fn (string $state): string => Email::getDirectionOptions()[$state] ?? $state),

                                TextEntry::make(Email::status)
                                    ->label('Status')
                                    ->badge()
                                    ->color(fn (string $state): string => match ($state) {
                                        Email::STATUS_DRAFT => 'gray',
                                        Email::STATUS_SENT => 'success',
                                        Email::STATUS_RECEIVED => 'info',
                                        Email::STATUS_FAILED => 'danger',
                                        Email::STATUS_READ => 'primary',
                                        default => 'gray',
                                    })
                                    ->formatStateUsing(fn (string $state): string => Email::getStatusOptions()[$state] ?? $state),

                                TextEntry::make('emailTemplate.name')
                                    ->label('E-Mail-Vorlage')
                                    ->placeholder('Keine Vorlage'),
                            ]),
                    ]),

                Section::make('Absender')
                    ->schema([
                        TextEntry::make(Email::from_email)
                            ->label('E-Mail-Adresse')
                            ->icon('heroicon-o-envelope')
                            ->copyable(),

                        TextEntry::make(Email::from_name)
                            ->label('Name')
                            ->icon('heroicon-o-user')
                            ->placeholder('Kein Name'),
                    ])
                    ->columns(2),

                Section::make('Empfänger')
                    ->schema([
                        TextEntry::make('customer.name')
                            ->label('Kunde')
                            ->icon('heroicon-o-user-circle')
                            ->placeholder('Kein Kunde verknüpft')
                            ->url(fn ($record) => $record->customer_id
                                ? CustomerResource::getViewUrl($record->customer)
                                : null),

                        TextEntry::make(Email::to_email)
                            ->label('E-Mail-Adresse')
                            ->icon('heroicon-o-envelope')
                            ->copyable(),

                        TextEntry::make(Email::to_name)
                            ->label('Name')
                            ->icon('heroicon-o-user')
                            ->placeholder('Kein Name'),

                        TextEntry::make(Email::cc)
                            ->label('CC')
                            ->placeholder('Keine')
                            ->columnSpan(1),

                        TextEntry::make(Email::bcc)
                            ->label('BCC')
                            ->placeholder('Keine')
                            ->columnSpan(1),

                        TextEntry::make(Email::reply_to)
                            ->label('Antwort an')
                            ->placeholder('Standard')
                            ->columnSpan(1),
                    ])
                    ->columns(3),

                Section::make('Nachricht')
                    ->schema([
                        TextEntry::make(Email::subject)
                            ->label('Betreff')
                            ->size(TextSize::Large)
                            ->weight('bold')
                            ->columnSpanFull(),

                        TextEntry::make(Email::body_html)
                            ->label('Inhalt')
                            ->html()
                            ->columnSpanFull(),

                        TextEntry::make(Email::body_text)
                            ->label('Text-Version')
                            ->placeholder('Keine Text-Version verfügbar')
                            ->columnSpanFull()
                            ->visible(fn ($record) => !empty($record->body_text)),
                    ]),

                Section::make('Anhänge')
                    ->schema([
                        RepeatableEntry::make('documents')
                            ->label('Angehängte Dokumente')
                            ->schema([
                                TextEntry::make(Document::type)
                                    ->label('Typ')
                                    ->badge()
                                    ->formatStateUsing(fn (Document $record) => $record->getTypeLabel())
                                    ->color(fn (string $state) => match ($state) {
                                        Document::TYPE_CONTRACT => 'success',
                                        Document::TYPE_INVOICE => 'warning',
                                        Document::TYPE_SEPA_MANDATE => 'info',
                                        Document::TYPE_REVOCATION_POLICY => 'primary',
                                        Document::TYPE_INFO_BROCHURE => 'primary',
                                        Document::TYPE_TERMS_CONDITIONS => 'primary',
                                        default => 'gray',
                                    }),

                                TextEntry::make(Document::title)
                                    ->label('Titel')
                                    ->url(fn (Document $record) => DocumentResource::getViewUrl($record))
                                    ->color('primary'),

                                TextEntry::make(Document::file_size)
                                    ->label('Größe')
                                    ->formatStateUsing(fn (Document $record) => $record->getFileSizeHuman()),

                                TextEntry::make(Document::file_path)
                                    ->label('Download')
                                    ->formatStateUsing(fn () => 'Herunterladen')
                                    ->url(fn (Document $record) => Storage::url($record->file_path))
                                    ->openUrlInNewTab()
                                    ->icon('heroicon-o-arrow-down-tray')
                                    ->color('success'),
                            ])
                            ->columns(4)
                            ->columnSpanFull(),
                    ])
                    ->visible(fn ($record) => $record->documents()->exists())
                    ->collapsible(),

                Section::make('Verknüpfungen')
                    ->schema([
                        TextEntry::make('rental.id')
                            ->label('Vermietung')
                            ->placeholder('Keine Vermietung verknüpft')
                            ->url(fn ($record) => $record->rental_id
                                ? route('filament.admin.resources.rentals.rentals.view', $record->rental_id)
                                : null),

                        TextEntry::make('user.name')
                            ->label('Benutzer')
                            ->placeholder('Kein Benutzer verknüpft')
                            ->icon('heroicon-o-user'),
                    ])
                    ->columns(2)
                    ->collapsible(),

                Section::make('Zeitstempel')
                    ->schema([
                        TextEntry::make(Email::sent_at)
                            ->label('Gesendet am')
                            ->dateTime('d.m.Y H:i')
                            ->placeholder('Nicht gesendet')
                            ->icon('heroicon-o-paper-airplane'),

                        TextEntry::make(Email::received_at)
                            ->label('Empfangen am')
                            ->dateTime('d.m.Y H:i')
                            ->placeholder('Nicht empfangen')
                            ->icon('heroicon-o-inbox'),

                        TextEntry::make(Email::read_at)
                            ->label('Gelesen am')
                            ->dateTime('d.m.Y H:i')
                            ->placeholder('Ungelesen')
                            ->icon('heroicon-o-eye'),

                        TextEntry::make('created_at')
                            ->label('Erstellt am')
                            ->dateTime('d.m.Y H:i')
                            ->icon('heroicon-o-calendar'),

                        TextEntry::make('updated_at')
                            ->label('Aktualisiert am')
                            ->dateTime('d.m.Y H:i')
                            ->icon('heroicon-o-pencil'),
                    ])
                    ->columns(3)
                    ->collapsible(),

                Section::make('Technische Details')
                    ->schema([
                        TextEntry::make(Email::message_id)
                            ->label('Message ID')
                            ->placeholder('Keine')
                            ->copyable(),

                        TextEntry::make(Email::in_reply_to)
                            ->label('Als Antwort auf')
                            ->placeholder('Keine'),

                        TextEntry::make(Email::references)
                            ->label('Referenzen')
                            ->placeholder('Keine'),

                        TextEntry::make(Email::error_message)
                            ->label('Fehlermeldung')
                            ->placeholder('Keine Fehler')
                            ->color('danger')
                            ->columnSpanFull()
                            ->visible(fn ($record) => !empty($record->error_message)),

                        KeyValueEntry::make(Email::metadata)
                            ->label('Metadaten')
                            ->columnSpanFull()
                            ->visible(fn ($record) => !empty($record->metadata)),
                    ])
                    ->columns(3)
                    ->collapsible()
                    ->collapsed(),
            ]);
    }
}
