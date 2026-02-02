<?php

namespace App\Filament\Admin\Resources\Inquiries\Tables;

use App\Models\Inquiry;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class InquiriesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->label('ID')
                    ->sortable(),

                TextColumn::make('board.name')
                    ->label('Board')
                    ->sortable()
                    ->searchable(),

                TextColumn::make(Inquiry::customer_name)
                    ->label('Kundenname')
                    ->sortable()
                    ->searchable(),

                TextColumn::make(Inquiry::customer_email)
                    ->label('E-Mail')
                    ->sortable()
                    ->searchable()
                    ->copyable(),

                TextColumn::make(Inquiry::start_date)
                    ->label('Startdatum')
                    ->date('d.m.Y')
                    ->sortable(),

                TextColumn::make(Inquiry::end_date)
                    ->label('Enddatum')
                    ->date('d.m.Y')
                    ->sortable(),

                TextColumn::make(Inquiry::status)
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
                    })
                    ->sortable(),

                TextColumn::make('created_at')
                    ->label('Erstellt am')
                    ->dateTime('d.m.Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make(Inquiry::status)
                    ->label('Status')
                    ->options([
                        Inquiry::STATUS_PENDING => 'Ausstehend',
                        Inquiry::STATUS_APPROVED => 'Genehmigt',
                        Inquiry::STATUS_REJECTED => 'Abgelehnt',
                        Inquiry::STATUS_CONVERTED => 'Vermietung erstellt',
                    ]),

                SelectFilter::make(Inquiry::board_id)
                    ->label('Board')
                    ->relationship('board', 'name')
                    ->searchable()
                    ->preload(),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }
}


