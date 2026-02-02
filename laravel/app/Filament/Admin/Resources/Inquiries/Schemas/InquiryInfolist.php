<?php

namespace App\Filament\Admin\Resources\Inquiries\Schemas;

use App\Models\Inquiry;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Infolists\Components\TextEntry;
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
                            ->label('Board'),

                        Group::make([
                            TextEntry::make(Inquiry::start_date)
                                ->label('Startdatum')
                                ->date('d.m.Y'),

                            TextEntry::make(Inquiry::end_date)
                                ->label('Enddatum')
                                ->date('d.m.Y'),
                        ])->columns(2),
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
                    ])
                    ->columns(3),

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


