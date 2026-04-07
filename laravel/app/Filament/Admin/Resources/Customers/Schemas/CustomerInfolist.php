<?php

namespace App\Filament\Admin\Resources\Customers\Schemas;

use App\Models\Customer;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class CustomerInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Kundeninformationen')
                    ->schema([
                        TextEntry::make(Customer::name)
                            ->label('Name'),

                        IconEntry::make(Customer::is_company)
                            ->label('Unternehmen')
                            ->boolean(),

                        TextEntry::make(Customer::company_name)
                            ->label('Unternehmensname')
                            ->default('—')
                            ->visible(fn ($record) => (bool) $record->{Customer::is_company}),
                    ])
                    ->columns(2),

                Section::make('Kontaktdaten')
                    ->schema([
                        TextEntry::make(Customer::email)
                            ->label('E-Mail')
                            ->copyable(),

                        TextEntry::make(Customer::phone)
                            ->label('Telefon')
                            ->placeholder('—'),
                    ])
                    ->columns(2),

                Section::make('Adresse')
                    ->schema([
                        TextEntry::make(Customer::street)
                            ->label('Straße')
                            ->placeholder('—'),

                        TextEntry::make(Customer::street_nr)
                            ->label('Hausnummer')
                            ->placeholder('—'),

                        TextEntry::make(Customer::zip)
                            ->label('PLZ')
                            ->placeholder('—'),

                        TextEntry::make(Customer::city)
                            ->label('Stadt')
                            ->placeholder('—'),
                    ])
                    ->columns(4),

                Section::make('Zahlung & Notizen')
                    ->schema([
                        TextEntry::make(Customer::payment_method)
                            ->label('Zahlungsmethode')
                            ->placeholder('—'),

                        TextEntry::make(Customer::notes)
                            ->label('Notizen')
                            ->placeholder('Keine Notizen')
                            ->columnSpanFull(),
                    ])
                    ->columns(1),

                Section::make('Statistiken')
                    ->schema([
                        TextEntry::make('rentals_count')
                            ->label('Vermietungen gesamt')
                            ->state(fn ($record) => $record->rentals()->count()),
                    ])
                    ->columns(1),

                Section::make('Zeitstempel')
                    ->schema([
                        TextEntry::make('created_at')
                            ->label('Erstellt am')
                            ->dateTime(),

                        TextEntry::make('updated_at')
                            ->label('Geändert am')
                            ->dateTime(),
                    ])
                    ->columns(2)
                    ->collapsible(),
            ]);
    }
}
