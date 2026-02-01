<?php

namespace App\Filament\Admin\Resources\Customers\Schemas;

use App\Models\Customer;
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
                Section::make('Customer Information')
                    ->schema([
                        TextInput::make(Customer::name)
                            ->label('Name')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('Full name'),

                        TextInput::make(Customer::company_name)
                            ->label('Company Name')
                            ->maxLength(255)
                            ->placeholder('Optional'),
                    ])
                    ->columns(2),

                Section::make('Contact Information')
                    ->schema([
                        TextInput::make(Customer::email)
                            ->label('Email')
                            ->email()
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255),

                        TextInput::make(Customer::phone)
                            ->label('Phone')
                            ->tel()
                            ->maxLength(255),

                        Textarea::make(Customer::address)
                            ->label('Address')
                            ->rows(3)
                            ->columnSpanFull(),
                    ])
                    ->columns(2),

                Section::make('Payment & Notes')
                    ->schema([
                        TextInput::make(Customer::payment_method)
                            ->label('Payment Method')
                            ->maxLength(255)
                            ->placeholder('z.B. Überweisung, Lastschrift'),

                        Textarea::make(Customer::notes)
                            ->label('Notes')
                            ->rows(4)
                            ->placeholder('Additional notes about the customer...')
                            ->columnSpanFull(),
                    ])
                    ->columns(1)
                    ->collapsible(),
            ]);
    }
}
