<?php

namespace App\Filament\Admin\Resources\Rentals\Tables;

use App\Filament\Admin\Resources\Rentals\Actions\RentalActions;
use App\Models\Customer;
use App\Models\Rental;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class RentalsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('customer.' . Customer::name)
                    ->label('Customer')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('customer.' . Customer::company_name)
                    ->label('Company')
                    ->searchable()
                    ->placeholder('—')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make(Rental::start_date)
                    ->label('Start Date')
                    ->date()
                    ->sortable(),

                TextColumn::make(Rental::end_date)
                    ->label('End Date')
                    ->date()
                    ->placeholder('Ongoing')
                    ->sortable(),

                TextColumn::make(Rental::total_price)
                    ->label('Price/Month')
                    ->money('EUR')
                    ->sortable(),

                TextColumn::make('fields_count')
                    ->label('Fields')
                    ->counts('fields')
                    ->sortable(),

                TextColumn::make(Rental::status)
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        Rental::STATUS_PENDING => 'warning',
                        Rental::STATUS_PAID => 'info',
                        Rental::STATUS_ACTIVE => 'success',
                        Rental::STATUS_COMPLETED => 'gray',
                        Rental::STATUS_CANCELLED => 'danger',
                        default => 'warning',
                    })
                    ->sortable(),

                TextColumn::make(Rental::paid_at)
                    ->label('Bezahlt am')
                    ->dateTime()
                    ->placeholder('Nicht bezahlt')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('content.access_code')
                    ->label('Zugangscode')
                    ->placeholder('—')
                    ->copyable()
                    ->copyMessage('Zugangscode kopiert!')
                    ->fontFamily('mono')
                    ->toggleable(),

                TextColumn::make('content.is_published')
                    ->label('Veröffentlicht')
                    ->badge()
                    ->color(fn ($state): string => $state ? 'success' : 'gray')
                    ->formatStateUsing(fn ($state): string => $state ? 'Ja' : 'Entwurf')
                    ->placeholder('Kein Content')
                    ->toggleable(),

                TextColumn::make(Rental::notes)
                    ->label('Notes')
                    ->limit(50)
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('created_at')
                    ->label('Created At')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make(Rental::status)
                    ->label('Status')
                    ->options([
                        Rental::STATUS_PENDING => 'Ausstehend',
                        Rental::STATUS_PAID => 'Bezahlt',
                        Rental::STATUS_ACTIVE => 'Aktiv',
                        Rental::STATUS_COMPLETED => 'Abgeschlossen',
                        Rental::STATUS_CANCELLED => 'Storniert',
                    ]),

                SelectFilter::make('has_content')
                    ->label('Content-Status')
                    ->options([
                        'with' => 'Mit Content',
                        'without' => 'Ohne Content',
                        'published' => 'Veröffentlicht',
                        'draft' => 'Entwurf',
                    ])
                    ->query(function ($query, array $data) {
                        return $query->when(
                            $data['value'] ?? null,
                            function ($query, $value) {
                                match ($value) {
                                    'with' => $query->has('content'),
                                    'without' => $query->doesntHave('content'),
                                    'published' => $query->whereHas('content', fn($q) => $q->where('is_published', true)),
                                    'draft' => $query->whereHas('content', fn($q) => $q->where('is_published', false)),
                                };
                            }
                        );
                    }),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
                RentalActions::markAsPaid(),
                RentalActions::viewAccessCode(),
                RentalActions::viewContentStatus(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort(Rental::start_date, 'desc');
    }
}
