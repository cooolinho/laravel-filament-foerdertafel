<?php

namespace App\Filament\Admin\Resources\RentalContents\Tables;

use App\Models\RentalContent;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class RentalContentsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort(RentalContent::review_requested_at, 'desc')
            ->columns([
                TextColumn::make('rental.customer.name')
                    ->label('Kunde')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('rental.customer.company_name')
                    ->label('Firma')
                    ->searchable()
                    ->placeholder('—'),

                TextColumn::make(RentalContent::access_code)
                    ->label('Zugangscode')
                    ->searchable()
                    ->fontFamily('mono')
                    ->copyable(),

                TextColumn::make(RentalContent::title)
                    ->label('Titel')
                    ->searchable()
                    ->limit(40)
                    ->placeholder('—'),

                IconColumn::make(RentalContent::needs_review)
                    ->label('Prüfung ausstehend')
                    ->boolean()
                    ->trueIcon('heroicon-o-clock')
                    ->falseIcon('heroicon-o-check-circle')
                    ->trueColor('warning')
                    ->falseColor('success'),

                IconColumn::make(RentalContent::is_published)
                    ->label('Veröffentlicht')
                    ->boolean()
                    ->trueIcon('heroicon-o-eye')
                    ->falseIcon('heroicon-o-eye-slash')
                    ->trueColor('success')
                    ->falseColor('gray'),

                TextColumn::make(RentalContent::review_requested_at)
                    ->label('Änderung beantragt am')
                    ->dateTime('d.m.Y H:i')
                    ->sortable()
                    ->placeholder('—'),
            ])
            ->filters([
                Filter::make('pending_review')
                    ->label('Nur ausstehende Prüfungen')
                    ->query(fn (Builder $query) => $query->where(RentalContent::needs_review, true))
                    ->default(),

                TernaryFilter::make(RentalContent::is_published)
                    ->label('Veröffentlicht'),
            ])
            ->recordActions([
                ViewAction::make(),
            ]);
    }
}

