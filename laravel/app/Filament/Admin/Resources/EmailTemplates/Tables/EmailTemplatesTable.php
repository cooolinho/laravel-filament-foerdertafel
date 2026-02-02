<?php

namespace App\Filament\Admin\Resources\EmailTemplates\Tables;

use App\Models\EmailTemplate;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class EmailTemplatesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make(EmailTemplate::name)
                    ->label('Name')
                    ->searchable()
                    ->sortable()
                    ->weight('medium'),

                TextColumn::make(EmailTemplate::slug)
                    ->label('Slug')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->copyable()
                    ->toggleable(),

                TextColumn::make(EmailTemplate::category)
                    ->label('Kategorie')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        EmailTemplate::CATEGORY_RENTAL => 'success',
                        EmailTemplate::CATEGORY_INQUIRY => 'info',
                        EmailTemplate::CATEGORY_SYSTEM => 'warning',
                        EmailTemplate::CATEGORY_MARKETING => 'primary',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string =>
                        EmailTemplate::getCategoryOptions()[$state] ?? $state
                    )
                    ->sortable()
                    ->searchable(),

                TextColumn::make(EmailTemplate::subject)
                    ->label('Betreff')
                    ->searchable()
                    ->sortable()
                    ->limit(50)
                    ->tooltip(fn ($record) => $record->subject),

                IconColumn::make(EmailTemplate::is_active)
                    ->label('Aktiv')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger')
                    ->sortable(),

                TextColumn::make('emails_count')
                    ->label('Verwendet')
                    ->counts('emails')
                    ->badge()
                    ->color('info')
                    ->sortable(),

                TextColumn::make('created_at')
                    ->label('Erstellt')
                    ->dateTime('d.m.Y')
                    ->sortable()
                    ->toggleable()
                    ->toggledHiddenByDefault(),

                TextColumn::make('updated_at')
                    ->label('Aktualisiert')
                    ->dateTime('d.m.Y H:i')
                    ->sortable()
                    ->toggleable(),
            ])
            ->filters([
                SelectFilter::make(EmailTemplate::category)
                    ->label('Kategorie')
                    ->options(EmailTemplate::getCategoryOptions())
                    ->native(false),

                TernaryFilter::make(EmailTemplate::is_active)
                    ->label('Status')
                    ->placeholder('Alle')
                    ->trueLabel('Nur aktive')
                    ->falseLabel('Nur inaktive')
                    ->queries(
                        true: fn ($query) => $query->where(EmailTemplate::is_active, true),
                        false: fn ($query) => $query->where(EmailTemplate::is_active, false),
                    ),
            ])
            ->recordActions([
                ViewAction::make(),
                Action::make('duplicate')
                    ->label('Duplizieren')
                    ->icon('heroicon-o-document-duplicate')
                    ->color('gray')
                    ->action(function (EmailTemplate $record) {
                        $newTemplate = $record->replicate();
                        $newTemplate->name = $record->name . ' (Kopie)';
                        $newTemplate->slug = $record->slug . '-copy-' . time();
                        $newTemplate->is_active = false;
                        $newTemplate->save();
                    })
                    ->successNotificationTitle('Vorlage wurde dupliziert'),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('name', 'asc');
    }
}
