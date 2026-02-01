<?php

namespace App\Filament\Resources\Rentals\Schemas;

use App\Models\Customer;
use App\Models\Field;
use App\Models\Rental;
use Filament\Forms\Components\DatePicker;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class RentalForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Rental Information')
                    ->schema([
                        Select::make(Rental::customer_id)
                            ->label('Customer')
                            ->relationship('customer', Customer::name)
                            ->required()
                            ->searchable()
                            ->preload()
                            ->createOptionForm([
                                TextInput::make(Customer::name)
                                    ->label('Name')
                                    ->required()
                                    ->maxLength(255),
                                TextInput::make(Customer::email)
                                    ->label('Email')
                                    ->email()
                                    ->required()
                                    ->maxLength(255),
                                TextInput::make(Customer::phone)
                                    ->label('Phone')
                                    ->tel()
                                    ->maxLength(255),
                            ]),

                        Select::make(Rental::status)
                            ->label('Status')
                            ->options([
                                Rental::STATUS_ACTIVE => 'Active',
                                Rental::STATUS_COMPLETED => 'Completed',
                                Rental::STATUS_CANCELLED => 'Cancelled',
                            ])
                            ->default(Rental::STATUS_ACTIVE)
                            ->required(),
                    ])
                    ->columns(2),

                Section::make('Rental Period')
                    ->schema([
                        DatePicker::make(Rental::start_date)
                            ->label('Start Date')
                            ->required()
                            ->native(false),

                        DatePicker::make(Rental::end_date)
                            ->label('End Date')
                            ->native(false)
                            ->placeholder('Leave empty for ongoing rental'),
                    ])
                    ->columns(2),

                Section::make('Fields')
                    ->schema([
                        Select::make('fields')
                            ->label('Rented Fields')
                            ->relationship('fields', Field::name)
                            ->multiple()
                            ->preload()
                            ->searchable()
                            ->required()
                            ->helperText('Select one or more adjacent fields'),
                    ])
                    ->columns(1),

                Section::make('Pricing')
                    ->schema([
                        TextInput::make(Rental::total_price)
                            ->label('Total Price per Month (€)')
                            ->required()
                            ->numeric()
                            ->prefix('€')
                            ->minValue(0)
                            ->step(0.01)
                            ->helperText('Will be calculated from selected fields'),
                    ])
                    ->columns(1),

                Section::make('Additional Information')
                    ->schema([
                        Textarea::make(Rental::notes)
                            ->label('Notes')
                            ->rows(4)
                            ->placeholder('Additional notes about the rental...'),
                    ])
                    ->columns(1)
                    ->collapsible(),
            ]);
    }
}
