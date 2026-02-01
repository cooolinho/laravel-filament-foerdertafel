<?php

namespace App\Filament\Admin\Resources\Customers\Schemas;

use App\Models\Customer;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class CustomerInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Customer Information')
                    ->schema([
                        TextEntry::make(Customer::name)
                            ->label('Name'),

                        TextEntry::make(Customer::company_name)
                            ->label('Company Name')
                            ->placeholder('—'),
                    ])
                    ->columns(2),

                Section::make('Contact Information')
                    ->schema([
                        TextEntry::make(Customer::email)
                            ->label('Email')
                            ->copyable(),

                        TextEntry::make(Customer::phone)
                            ->label('Phone')
                            ->placeholder('—'),

                        TextEntry::make(Customer::address)
                            ->label('Address')
                            ->placeholder('—')
                            ->columnSpanFull(),
                    ])
                    ->columns(2),

                Section::make('Payment & Notes')
                    ->schema([
                        TextEntry::make(Customer::payment_method)
                            ->label('Payment Method')
                            ->placeholder('—'),

                        TextEntry::make(Customer::notes)
                            ->label('Notes')
                            ->placeholder('No notes')
                            ->columnSpanFull(),
                    ])
                    ->columns(1),

                Section::make('Statistics')
                    ->schema([
                        TextEntry::make('rentals_count')
                            ->label('Total Rentals')
                            ->state(fn ($record) => $record->rentals()->count()),
                    ])
                    ->columns(1),

                Section::make('Timestamps')
                    ->schema([
                        TextEntry::make('created_at')
                            ->label('Created At')
                            ->dateTime(),

                        TextEntry::make('updated_at')
                            ->label('Updated At')
                            ->dateTime(),
                    ])
                    ->columns(2)
                    ->collapsible(),
            ]);
    }
}
