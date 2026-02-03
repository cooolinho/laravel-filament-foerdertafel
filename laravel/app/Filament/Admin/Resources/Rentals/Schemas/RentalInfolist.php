<?php

namespace App\Filament\Admin\Resources\Rentals\Schemas;

use App\Models\Customer;
use App\Models\Field;
use App\Models\Rental;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class RentalInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Rental Information')
                    ->schema([
                        TextEntry::make('customer.' . Customer::name)
                            ->label('Customer'),

                        TextEntry::make('customer.' . Customer::company_name)
                            ->label('Company')
                            ->placeholder('—'),

                        TextEntry::make(Rental::status)
                            ->label('Status')
                            ->badge()
                            ->color(fn (string $state): string => match ($state) {
                                Rental::STATUS_ACTIVE => 'success',
                                Rental::STATUS_COMPLETED => 'gray',
                                Rental::STATUS_CANCELLED => 'danger',
                                default => 'warning',
                            }),
                    ])
                    ->columns(3),

                Section::make('Rental Period')
                    ->schema([
                        TextEntry::make(Rental::start_date)
                            ->label('Start Date')
                            ->date(),

                        TextEntry::make(Rental::end_date)
                            ->label('End Date')
                            ->date()
                            ->placeholder('Ongoing'),
                    ])
                    ->columns(2),

                Section::make('Pricing')
                    ->schema([
                        TextEntry::make(Rental::total_price)
                            ->label('Total Price per Month')
                            ->money('EUR'),

                        TextEntry::make('fields_count')
                            ->label('Number of Fields')
                            ->state(fn ($record) => $record->fields()->count()),
                    ])
                    ->columns(2),

                Section::make('Rented Fields')
                    ->schema([
                        RepeatableEntry::make('fields')
                            ->label('')
                            ->schema([
                                TextEntry::make(Field::name)
                                    ->label('Field Name'),

                                TextEntry::make(Field::row)
                                    ->label('Row')
                                    ->numeric(),

                                TextEntry::make(Field::column)
                                    ->label('Column')
                                    ->numeric(),

                                TextEntry::make(Field::price_per_month)
                                    ->label('Price/Month')
                                    ->money('EUR'),
                            ])
                            ->columns(4),
                    ]),

                Section::make('Additional Information')
                    ->schema([
                        TextEntry::make(Rental::notes)
                            ->label('Notes')
                            ->placeholder('No notes'),

                        TextEntry::make(Rental::paid_at)
                            ->label('Paid At')
                            ->dateTime()
                            ->placeholder('Not paid yet'),
                    ])
                    ->columns(2),

                Section::make('Customer Portal Content')
                    ->schema([
                        TextEntry::make('content.access_code')
                            ->label('Access Code')
                            ->placeholder('No content initialized')
                            ->copyable()
                            ->copyMessage('Access code copied!')
                            ->fontFamily('mono')
                            ->size('lg')
                            ->weight('bold'),

                        TextEntry::make('content.is_published')
                            ->label('Published Status')
                            ->badge()
                            ->color(fn ($state): string => $state ? 'success' : 'gray')
                            ->formatStateUsing(fn ($state): string => $state ? 'Published' : 'Draft')
                            ->placeholder('No content'),

                        TextEntry::make('content.title')
                            ->label('Title')
                            ->placeholder('—'),

                        TextEntry::make('content.website_url')
                            ->label('Website')
                            ->placeholder('—')
                            ->url(fn ($state) => $state)
                            ->openUrlInNewTab(),

                        TextEntry::make('content.contact_email')
                            ->label('Contact Email')
                            ->placeholder('—')
                            ->copyable(),

                        TextEntry::make('content.contact_phone')
                            ->label('Contact Phone')
                            ->placeholder('—')
                            ->copyable(),

                        TextEntry::make('content.last_accessed_at')
                            ->label('Last Accessed')
                            ->dateTime()
                            ->placeholder('Never accessed')
                            ->since(),

                        TextEntry::make('content.company_logo')
                            ->label('Logo')
                            ->placeholder('No logo')
                            ->formatStateUsing(fn ($state) => $state ? 'Uploaded' : 'No logo')
                            ->badge()
                            ->color(fn ($state) => $state ? 'success' : 'gray'),
                    ])
                    ->columns(4)
                    ->visible(fn ($record) => $record->content !== null)
                    ->collapsible(),

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
