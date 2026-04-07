<?php

namespace App\Filament\Admin\Resources\Customers\Schemas;

use App\Models\Customer;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class CustomerForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Kundeninformationen')
                    ->schema([
                        TextInput::make(Customer::name)
                            ->label('Name')
                            ->required()
                            ->maxLength(255),

                        Checkbox::make(Customer::is_company)
                            ->label('Unternehmen')
                            ->reactive(),

                        TextInput::make(Customer::company_name)
                            ->label('Unternehmensname')
                            ->maxLength(255)
                            ->visible(fn ($get) => (bool) $get(Customer::is_company))
                            ->required(fn ($get) => (bool) $get(Customer::is_company)),
                    ])
                    ->columns(2),

                Section::make('Kontaktdaten')
                    ->schema([
                        TextInput::make(Customer::email)
                            ->label('E-Mail')
                            ->email()
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255),

                        TextInput::make(Customer::phone)
                            ->label('Telefon')
                            ->tel()
                            ->maxLength(255),
                    ])
                    ->columns(2),

                Section::make('Adresse')
                    ->schema([
                        TextInput::make(Customer::street)
                            ->label('Straße')
                            ->maxLength(255),

                        TextInput::make(Customer::street_nr)
                            ->label('Hausnummer')
                            ->maxLength(20),

                        TextInput::make(Customer::zip)
                            ->label('PLZ')
                            ->maxLength(10),

                        TextInput::make(Customer::city)
                            ->label('Stadt')
                            ->maxLength(255),
                    ])
                    ->columns(2),

                Section::make('Zahlung & Notizen')
                    ->schema([
                        TextInput::make(Customer::payment_method)
                            ->label('Zahlungsmethode')
                            ->maxLength(255)
                            ->placeholder('z.B. Überweisung, Lastschrift'),

                        Textarea::make(Customer::notes)
                            ->label('Notizen')
                            ->rows(4)
                            ->columnSpanFull(),
                    ])
                    ->columns(1)
                    ->collapsible(),
            ]);
    }
}
