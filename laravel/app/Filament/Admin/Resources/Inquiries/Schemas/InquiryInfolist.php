<?php

namespace App\Filament\Admin\Resources\Inquiries\Schemas;

use App\Filament\Admin\Resources\Boards\BoardResource;
use App\Models\Inquiry;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class InquiryInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Anfrage Details')
                    ->schema([
                        TextEntry::make(Inquiry::status)
                            ->label('Status')
                            ->badge()
                            ->color(fn (string $state): string => match ($state) {
                                Inquiry::STATUS_PENDING => 'warning',
                                Inquiry::STATUS_APPROVED => 'success',
                                Inquiry::STATUS_REJECTED => 'danger',
                                Inquiry::STATUS_CONVERTED => 'info',
                                default => 'gray',
                            })
                            ->formatStateUsing(fn (string $state): string => match ($state) {
                                Inquiry::STATUS_PENDING => 'Ausstehend',
                                Inquiry::STATUS_APPROVED => 'Genehmigt',
                                Inquiry::STATUS_REJECTED => 'Abgelehnt',
                                Inquiry::STATUS_CONVERTED => 'Vermietung erstellt',
                                default => $state,
                            }),

                        TextEntry::make('board.name')
                            ->label('Board')
                            ->url(fn (Inquiry $record) => BoardResource::getViewUrl($record->board))
                            ->openUrlInNewTab(),

                        Group::make([
                            TextEntry::make(Inquiry::start_date)
                                ->label('Startdatum')
                                ->date('d.m.Y'),

                            TextEntry::make(Inquiry::end_date)
                                ->label('Enddatum')
                                ->date('d.m.Y'),

                            TextEntry::make(Inquiry::rental_months)
                                ->label('Mietdauer')
                                ->suffix(fn ($state) => $state === 1 ? ' Monat' : ' Monate'),
                        ])->columns(3),
                    ]),

                Section::make('Gewünschte Felder')
                    ->schema([
                        TextEntry::make('fields.name')
                            ->label('Felder')
                            ->listWithLineBreaks()
                            ->bulleted(),
                    ]),

                Section::make('Kundendaten')
                    ->schema([
                        TextEntry::make(Inquiry::customer_name)
                            ->label('Name'),

                        TextEntry::make(Inquiry::customer_email)
                            ->label('E-Mail')
                            ->copyable()
                            ->icon('heroicon-o-envelope'),

                        TextEntry::make(Inquiry::customer_phone)
                            ->label('Telefon')
                            ->copyable()
                            ->icon('heroicon-o-phone'),

                        IconEntry::make(Inquiry::is_company)
                            ->label('Unternehmen')
                            ->boolean(),

                        TextEntry::make(Inquiry::company_name)
                            ->label('Unternehmensname')
                            ->default('—')
                            ->visible(fn ($record) => (bool) $record->{Inquiry::is_company}),
                    ])
                    ->columns(3),

                Section::make('Adresse')
                    ->schema([
                        TextEntry::make(Inquiry::street)
                            ->label('Straße'),

                        TextEntry::make(Inquiry::street_nr)
                            ->label('Hausnummer'),

                        TextEntry::make(Inquiry::zip)
                            ->label('PLZ'),

                        TextEntry::make(Inquiry::city)
                            ->label('Stadt'),
                    ])
                    ->columns(4),

                Section::make('Nachrichten')
                    ->schema([
                        TextEntry::make(Inquiry::message)
                            ->label('Nachricht vom Kunden')
                            ->default('Keine Nachricht')
                            ->columnSpanFull(),

                        TextEntry::make(Inquiry::admin_notes)
                            ->label('Admin Notizen')
                            ->default('Keine Notizen')
                            ->columnSpanFull(),
                    ]),

                Section::make('Anhänge')
                    ->schema([
                        TextEntry::make(Inquiry::attachments)
                            ->label('Hochgeladene Dateien')
                            ->default('Keine Anhänge vorhanden')
                            ->formatStateUsing(fn ($state) => basename($state))
                            ->listWithLineBreaks()
                            ->bulleted()
                            ->url(fn ($state, $record) => $state
                                ? route('inquiry.attachment.download', [
                                    'inquiry' => $record->id,
                                    'filename' => basename($state),
                                ])
                                : null
                            )
                            ->openUrlInNewTab()
                            ->columnSpanFull(),
                    ])
                    ->visible(fn ($record) => !empty($record->{Inquiry::attachments})),

                Section::make('Vermietung')
                    ->schema([
                        TextEntry::make('rental.id')
                            ->label('Vermietungs-ID')
                            ->default('Noch keine Vermietung erstellt'),
                    ])
                    ->visible(fn ($record) => $record->rental_id !== null),
            ]);
    }
}


