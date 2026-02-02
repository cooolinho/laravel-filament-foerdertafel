<?php

namespace App\Filament\Admin\Resources\Emails\Tables;

use App\Models\Email;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class EmailsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make(Email::direction)
                    ->label('Richtung')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        Email::DIRECTION_INBOUND => 'info',
                        Email::DIRECTION_OUTBOUND => 'success',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        Email::DIRECTION_INBOUND => '↓',
                        Email::DIRECTION_OUTBOUND => '↑',
                        default => $state,
                    })
                    ->tooltip(fn (string $state): string => Email::getDirectionOptions()[$state] ?? $state)
                    ->sortable()
                    ->searchable(),

                IconColumn::make(Email::read_at)
                    ->label('Status')
                    ->boolean()
                    ->trueIcon('heroicon-o-envelope-open')
                    ->falseIcon('heroicon-o-envelope')
                    ->trueColor('gray')
                    ->falseColor('primary')
                    ->tooltip(fn ($record) => $record->read_at ? 'Gelesen' : 'Ungelesen')
                    ->sortable(),

                TextColumn::make(Email::status)
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        Email::STATUS_DRAFT => 'gray',
                        Email::STATUS_SENT => 'success',
                        Email::STATUS_RECEIVED => 'info',
                        Email::STATUS_FAILED => 'danger',
                        Email::STATUS_READ => 'primary',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => Email::getStatusOptions()[$state] ?? $state)
                    ->sortable()
                    ->searchable(),

                TextColumn::make(Email::from_email)
                    ->label('Von')
                    ->searchable()
                    ->sortable()
                    ->formatStateUsing(fn ($record) => $record->from_name
                        ? $record->from_name . ' <' . $record->from_email . '>'
                        : $record->from_email)
                    ->limit(30)
                    ->tooltip(fn ($record) => $record->from_name
                        ? $record->from_name . ' <' . $record->from_email . '>'
                        : $record->from_email),

                TextColumn::make(Email::to_email)
                    ->label('An')
                    ->searchable()
                    ->sortable()
                    ->formatStateUsing(fn ($record) => $record->to_name
                        ? $record->to_name . ' <' . $record->to_email . '>'
                        : $record->to_email)
                    ->limit(30)
                    ->tooltip(fn ($record) => $record->to_name
                        ? $record->to_name . ' <' . $record->to_email . '>'
                        : $record->to_email),

                TextColumn::make(Email::subject)
                    ->label('Betreff')
                    ->searchable()
                    ->sortable()
                    ->limit(50)
                    ->weight('medium')
                    ->tooltip(fn ($record) => $record->subject),

                TextColumn::make('customer.name')
                    ->label('Kunde')
                    ->searchable()
                    ->sortable()
                    ->toggleable()
                    ->placeholder('Kein Kunde'),

                TextColumn::make('emailTemplate.name')
                    ->label('Vorlage')
                    ->toggleable()
                    ->toggledHiddenByDefault()
                    ->placeholder('Keine'),

                TextColumn::make(Email::sent_at)
                    ->label('Gesendet')
                    ->dateTime('d.m.Y H:i')
                    ->sortable()
                    ->toggleable()
                    ->placeholder('–'),

                TextColumn::make(Email::received_at)
                    ->label('Empfangen')
                    ->dateTime('d.m.Y H:i')
                    ->sortable()
                    ->toggleable()
                    ->placeholder('–'),

                TextColumn::make('created_at')
                    ->label('Erstellt')
                    ->dateTime('d.m.Y H:i')
                    ->sortable()
                    ->toggleable()
                    ->toggledHiddenByDefault(),
            ])
            ->filters([
                SelectFilter::make(Email::direction)
                    ->label('Richtung')
                    ->options(Email::getDirectionOptions())
                    ->native(false),

                SelectFilter::make(Email::status)
                    ->label('Status')
                    ->options(Email::getStatusOptions())
                    ->native(false),

                TernaryFilter::make(Email::read_at)
                    ->label('Gelesen')
                    ->placeholder('Alle')
                    ->trueLabel('Nur gelesene')
                    ->falseLabel('Nur ungelesene')
                    ->queries(
                        true: fn ($query) => $query->whereNotNull(Email::read_at),
                        false: fn ($query) => $query->whereNull(Email::read_at),
                    ),

                SelectFilter::make(Email::customer_id)
                    ->label('Kunde')
                    ->relationship('customer', 'name')
                    ->searchable()
                    ->preload()
                    ->native(false),

                SelectFilter::make(Email::email_template_id)
                    ->label('Vorlage')
                    ->relationship('emailTemplate', 'name')
                    ->searchable()
                    ->preload()
                    ->native(false),
            ])
            ->recordActions([
                ViewAction::make(),
                Action::make('markAsRead')
                    ->label('Als gelesen markieren')
                    ->icon('heroicon-o-envelope-open')
                    ->color('success')
                    ->visible(fn (Email $record) => !$record->isRead())
                    ->action(function (Email $record) {
                        $record->markAsRead();
                        Notification::make()
                            ->title('Als gelesen markiert')
                            ->success()
                            ->send();
                    }),
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
