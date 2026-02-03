<?php

namespace App\Filament\Admin\Widgets;

use App\Filament\Admin\Resources\Inquiries\InquiryResource;
use App\Models\Inquiry;
use Filament\Actions\Action;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class RecentInquiriesWidget extends BaseWidget
{
    protected int | string | array $columnSpan = 'full';

    protected static ?int $sort = 2;

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Inquiry::query()
                    ->latest()
                    ->limit(10)
            )
            ->columns([
                Tables\Columns\TextColumn::make('customer_name')
                    ->label('Kunde')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('customer_email')
                    ->label('E-Mail')
                    ->searchable()
                    ->copyable()
                    ->icon('heroicon-m-envelope'),

                Tables\Columns\TextColumn::make('board.name')
                    ->label('Tafel')
                    ->sortable(),

                Tables\Columns\TextColumn::make('start_date')
                    ->label('Von')
                    ->date('d.m.Y')
                    ->sortable(),

                Tables\Columns\TextColumn::make('end_date')
                    ->label('Bis')
                    ->date('d.m.Y')
                    ->sortable(),

                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->colors([
                        'warning' => Inquiry::STATUS_PENDING,
                        'success' => Inquiry::STATUS_APPROVED,
                        'danger' => Inquiry::STATUS_REJECTED,
                        'info' => Inquiry::STATUS_CONVERTED,
                    ])
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        Inquiry::STATUS_PENDING => 'Ausstehend',
                        Inquiry::STATUS_APPROVED => 'Genehmigt',
                        Inquiry::STATUS_REJECTED => 'Abgelehnt',
                        Inquiry::STATUS_CONVERTED => 'Umgewandelt',
                        default => $state,
                    }),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Erstellt')
                    ->dateTime('d.m.Y H:i')
                    ->sortable(),
            ])
            ->heading('Neueste Anfragen')
            ->recordActions([
                Action::make('view')
                    ->label('Ansehen')
                    ->icon('heroicon-m-eye')
                    ->url(fn (Inquiry $record): string => InquiryResource::getViewUrl($record))
            ]);
    }
}
