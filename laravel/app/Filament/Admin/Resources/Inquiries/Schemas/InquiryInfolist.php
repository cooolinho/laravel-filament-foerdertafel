<?php

namespace App\Filament\Admin\Resources\Inquiries\Schemas;

use App\Filament\Admin\Resources\Boards\BoardResource;
use App\Filament\Admin\Resources\Rentals\RentalResource;
use App\Models\Inquiry;
use App\Models\Rental;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class InquiryInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([

                // ── 1. Anfrage-Übersicht ─────────────────────────────────────
                Section::make('Anfrage-Übersicht')
                    ->icon('heroicon-o-document-text')
                    ->schema([
                        TextEntry::make(Inquiry::status)
                            ->label('Status')
                            ->badge()
                            ->color(fn (string $state): string => match ($state) {
                                Inquiry::STATUS_PENDING   => 'warning',
                                Inquiry::STATUS_APPROVED  => 'success',
                                Inquiry::STATUS_REJECTED  => 'danger',
                                Inquiry::STATUS_CONVERTED => 'info',
                                default                   => 'gray',
                            })
                            ->formatStateUsing(fn (string $state): string => match ($state) {
                                Inquiry::STATUS_PENDING   => 'Ausstehend',
                                Inquiry::STATUS_APPROVED  => 'Genehmigt',
                                Inquiry::STATUS_REJECTED  => 'Abgelehnt',
                                Inquiry::STATUS_CONVERTED => 'Vermietung erstellt',
                                default                   => $state,
                            }),

                        TextEntry::make('board.name')
                            ->label('Board')
                            ->icon('heroicon-o-clipboard-document-list')
                            ->url(fn (Inquiry $record) => BoardResource::getViewUrl($record->board))
                            ->openUrlInNewTab(),

                        TextEntry::make('created_at')
                            ->label('Eingegangen am')
                            ->dateTime('d.m.Y H:i')
                            ->icon('heroicon-o-calendar'),

                        TextEntry::make('updated_at')
                            ->label('Zuletzt aktualisiert')
                            ->dateTime('d.m.Y H:i')
                            ->icon('heroicon-o-clock'),
                    ])
                    ->columns(4),

                // ── 2. Mietdetails ───────────────────────────────────────────
                Section::make('Mietdetails')
                    ->icon('heroicon-o-calendar-days')
                    ->schema([
                        TextEntry::make(Inquiry::start_date)
                            ->label('Startdatum')
                            ->date('d.m.Y')
                            ->icon('heroicon-o-play-circle'),

                        TextEntry::make(Inquiry::end_date)
                            ->label('Enddatum')
                            ->date('d.m.Y')
                            ->icon('heroicon-o-stop-circle'),

                        TextEntry::make(Inquiry::rental_months)
                            ->label('Mietdauer')
                            ->icon('heroicon-o-clock')
                            ->suffix(fn ($state) => $state === 1 ? ' Monat' : ' Monate'),
                    ])
                    ->columns(3),

                // ── 3. Gewünschte Felder ─────────────────────────────────────
                Section::make('Gewünschte Felder')
                    ->icon('heroicon-o-squares-2x2')
                    ->schema([
                        TextEntry::make('fields.name')
                            ->label('Felder')
                            ->listWithLineBreaks()
                            ->bulleted()
                            ->placeholder('Keine Felder ausgewählt'),
                    ]),

                // ── 4. Kundendaten ───────────────────────────────────────────
                Section::make('Kundendaten')
                    ->icon('heroicon-o-user')
                    ->schema([
                        TextEntry::make(Inquiry::customer_name)
                            ->label('Name')
                            ->icon('heroicon-o-user')
                            ->weight('bold'),

                        TextEntry::make(Inquiry::customer_email)
                            ->label('E-Mail')
                            ->copyable()
                            ->icon('heroicon-o-envelope'),

                        TextEntry::make(Inquiry::customer_phone)
                            ->label('Telefon')
                            ->copyable()
                            ->placeholder('—')
                            ->icon('heroicon-o-phone'),

                        IconEntry::make(Inquiry::is_company)
                            ->label('Unternehmen')
                            ->boolean(),

                        TextEntry::make(Inquiry::company_name)
                            ->label('Unternehmensname')
                            ->placeholder('—')
                            ->icon('heroicon-o-building-office')
                            ->visible(fn ($record) => (bool) $record->{Inquiry::is_company}),
                    ])
                    ->columns(3),

                // ── 5. Postanschrift ─────────────────────────────────────────
                Section::make('Postanschrift')
                    ->icon('heroicon-o-map-pin')
                    ->schema([
                        TextEntry::make(Inquiry::street)
                            ->label('Straße')
                            ->placeholder('—'),

                        TextEntry::make(Inquiry::street_nr)
                            ->label('Hausnummer')
                            ->placeholder('—'),

                        TextEntry::make(Inquiry::zip)
                            ->label('PLZ')
                            ->placeholder('—'),

                        TextEntry::make(Inquiry::city)
                            ->label('Stadt')
                            ->placeholder('—'),
                    ])
                    ->columns(4),

                // ── 6. Rechnungsanschrift ────────────────────────────────────
                Section::make('Rechnungsanschrift')
                    ->icon('heroicon-o-document-duplicate')
                    ->schema([
                        IconEntry::make(Inquiry::billing_use_postal_address)
                            ->label('Identisch mit Postanschrift')
                            ->boolean()
                            ->columnSpanFull(),

                        TextEntry::make(Inquiry::billing_street)
                            ->label('Straße')
                            ->placeholder('—')
                            ->visible(fn ($record) => !(bool) $record->{Inquiry::billing_use_postal_address}),

                        TextEntry::make(Inquiry::billing_address2)
                            ->label('Adresszusatz')
                            ->placeholder('—')
                            ->visible(fn ($record) => !(bool) $record->{Inquiry::billing_use_postal_address}),

                        TextEntry::make(Inquiry::billing_zip)
                            ->label('PLZ')
                            ->placeholder('—')
                            ->visible(fn ($record) => !(bool) $record->{Inquiry::billing_use_postal_address}),

                        TextEntry::make(Inquiry::billing_city)
                            ->label('Stadt')
                            ->placeholder('—')
                            ->visible(fn ($record) => !(bool) $record->{Inquiry::billing_use_postal_address}),

                        TextEntry::make(Inquiry::billing_country)
                            ->label('Land')
                            ->placeholder('—')
                            ->visible(fn ($record) => !(bool) $record->{Inquiry::billing_use_postal_address}),
                    ])
                    ->columns(4),

                // ── 7. Zahlungsdaten ─────────────────────────────────────────
                Section::make('Zahlungsdaten')
                    ->icon('heroicon-o-credit-card')
                    ->schema([
                        TextEntry::make(Inquiry::payment_method)
                            ->label('Zahlungsmethode')
                            ->badge()
                            ->color(fn (string $state): string => match ($state) {
                                Inquiry::PAYMENT_METHOD_SEPA => 'info',
                                default                      => 'gray',
                            })
                            ->formatStateUsing(fn (string $state): string => match ($state) {
                                Inquiry::PAYMENT_METHOD_SEPA => 'SEPA-Lastschrift',
                                default                      => $state,
                            }),

                        IconEntry::make(Inquiry::sepa_mandate_accepted)
                            ->label('SEPA-Mandat akzeptiert')
                            ->boolean(),

                        TextEntry::make(Inquiry::account_holder)
                            ->label('Kontoinhaber')
                            ->placeholder('—')
                            ->icon('heroicon-o-user'),

                        TextEntry::make(Inquiry::bank_name)
                            ->label('Bank')
                            ->placeholder('—')
                            ->icon('heroicon-o-building-library'),

                        TextEntry::make(Inquiry::iban)
                            ->label('IBAN')
                            ->copyable()
                            ->placeholder('—')
                            ->formatStateUsing(
                                fn ($state) => $state
                                    ? implode(' ', str_split(preg_replace('/\s+/', '', $state), 4))
                                    : null
                            ),

                        TextEntry::make(Inquiry::bic)
                            ->label('BIC')
                            ->copyable()
                            ->placeholder('—'),
                    ])
                    ->columns(3),

                // ── 8. Nachrichten ───────────────────────────────────────────
                Section::make('Nachrichten')
                    ->icon('heroicon-o-chat-bubble-left-right')
                    ->schema([
                        TextEntry::make(Inquiry::message)
                            ->label('Nachricht vom Kunden')
                            ->placeholder('Keine Nachricht')
                            ->columnSpanFull(),

                        TextEntry::make(Inquiry::admin_notes)
                            ->label('Admin Notizen')
                            ->placeholder('Keine Notizen')
                            ->columnSpanFull(),
                    ]),

                // ── 9. Anhänge ───────────────────────────────────────────────
                Section::make('Anhänge')
                    ->icon('heroicon-o-paper-clip')
                    ->collapsible()
                    ->schema([
                        TextEntry::make(Inquiry::attachments)
                            ->label('Hochgeladene Dateien')
                            ->placeholder('Keine Anhänge vorhanden')
                            ->formatStateUsing(fn ($state) => basename($state))
                            ->listWithLineBreaks()
                            ->bulleted()
                            ->url(fn ($state, $record) => $state
                                ? route('inquiry.attachment.download', [
                                    'inquiry'  => $record->id,
                                    'filename' => basename($state),
                                ])
                                : null
                            )
                            ->openUrlInNewTab()
                            ->columnSpanFull(),
                    ])
                    ->visible(fn ($record) => !empty($record->{Inquiry::attachments})),

                // ── 10. Verknüpfte Vermietung ────────────────────────────────
                Section::make('Verknüpfte Vermietung')
                    ->icon('heroicon-o-link')
                    ->schema([
                        TextEntry::make('rental.id')
                            ->label('Vermietungs-ID')
                            ->badge()
                            ->color('success')
                            ->url(fn (Inquiry $record) => $record->rental_id
                                ? RentalResource::getViewUrl($record->rental)
                                : null
                            )
                            ->openUrlInNewTab(),

                        TextEntry::make('rental.status')
                            ->label('Status der Vermietung')
                            ->badge()
                            ->color(fn ($state): string => match ($state) {
                                Rental::STATUS_ACTIVE    => 'success',
                                Rental::STATUS_COMPLETED => 'gray',
                                Rental::STATUS_CANCELLED => 'danger',
                                default                  => 'warning',
                            })
                            ->formatStateUsing(fn ($state): string => match ($state) {
                                Rental::STATUS_ACTIVE    => 'Aktiv',
                                Rental::STATUS_COMPLETED => 'Abgeschlossen',
                                Rental::STATUS_CANCELLED => 'Storniert',
                                default                  => $state ?? '—',
                            }),
                    ])
                    ->columns(2)
                    ->visible(fn ($record) => $record->rental_id !== null),

            ]);
    }
}
