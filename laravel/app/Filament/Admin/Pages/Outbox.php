<?php

namespace App\Filament\Admin\Pages;

use App\Filament\Admin\Resources\Emails\EmailResource;
use App\Jobs\SendEmailJob;
use App\Models\Email;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use UnitEnum;

class Outbox extends Page implements HasTable
{
    use InteractsWithTable;

    protected static string|null|BackedEnum $navigationIcon = Heroicon::OutlinedPaperAirplane;

    protected string $view = 'filament.admin.pages.outbox';

    protected static ?string $navigationLabel = 'Postausgang';

    protected static ?string $title = 'Postausgang';

    protected static string|null|UnitEnum $navigationGroup = 'Kommunikation';

    protected static ?int $navigationSort = 4;

    public static function getNavigationBadge(): ?string
    {
        $draftCount = Email::outbound()->draft()->count();
        return $draftCount > 0 ? (string) $draftCount : null;
    }

    public static function getNavigationBadgeColor(): string|array|null
    {
        return 'warning';
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(Email::query()->outbound())
            ->columns([
                TextColumn::make(Email::status)
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        Email::STATUS_DRAFT => 'gray',
                        Email::STATUS_SENT => 'success',
                        Email::STATUS_FAILED => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => Email::getStatusOptions()[$state] ?? $state)
                    ->sortable(),

                TextColumn::make(Email::to_email)
                    ->label('An')
                    ->searchable()
                    ->sortable()
                    ->formatStateUsing(fn ($record) => $record->to_name
                        ?: $record->to_email)
                    ->description(fn ($record) => $record->to_name
                        ? $record->to_email
                        : null)
                    ->weight('medium'),

                TextColumn::make(Email::subject)
                    ->label('Betreff')
                    ->searchable()
                    ->sortable()
                    ->limit(60)
                    ->tooltip(fn ($record) => $record->subject)
                    ->description(fn ($record) => strip_tags(substr($record->body_html ?? $record->body_text, 0, 100)) . '...'),

                TextColumn::make('customer.name')
                    ->label('Kunde')
                    ->searchable()
                    ->sortable()
                    ->placeholder('Unbekannt')
                    ->toggleable(),

                TextColumn::make('emailTemplate.name')
                    ->label('Vorlage')
                    ->toggleable()
                    ->placeholder('Keine'),

                IconColumn::make(Email::read_at)
                    ->label('Gelesen')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('gray')
                    ->tooltip(fn ($record) => $record->read_at ? 'Gelesen am ' . $record->read_at->format('d.m.Y H:i') : 'Nicht gelesen')
                    ->toggleable(),

                TextColumn::make(Email::sent_at)
                    ->label('Gesendet')
                    ->dateTime('d.m.Y H:i')
                    ->sortable()
                    ->placeholder('Noch nicht gesendet')
                    ->description(fn ($record) => $record->sent_at?->diffForHumans()),

                TextColumn::make('user.name')
                    ->label('Erstellt von')
                    ->toggleable()
                    ->toggledHiddenByDefault()
                    ->placeholder('System'),
            ])
            ->filters([
                SelectFilter::make(Email::status)
                    ->label('Status')
                    ->options([
                        Email::STATUS_DRAFT => Email::getStatusOptions()[Email::STATUS_DRAFT],
                        Email::STATUS_SENT => Email::getStatusOptions()[Email::STATUS_SENT],
                        Email::STATUS_FAILED => Email::getStatusOptions()[Email::STATUS_FAILED],
                    ])
                    ->native(false),

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
                ViewAction::make()
                    ->url(fn (Email $record): string => EmailResource::getViewUrl($record)),

                EditAction::make()
                    ->visible(fn (Email $record) => $record->isDraft())
                    ->url(fn (Email $record): string => EmailResource::getEditUrl($record)),

                Action::make('send')
                    ->label('Senden')
                    ->icon('heroicon-o-paper-airplane')
                    ->color('success')
                    ->visible(fn (Email $record) => $record->isDraft())
                    ->requiresConfirmation()
                    ->modalHeading('E-Mail senden')
                    ->modalDescription('Möchten Sie diese E-Mail wirklich senden?')
                    ->modalSubmitActionLabel('Ja, senden')
                    ->action(function (Email $record) {
                        // Dispatche den SendEmailJob
                        SendEmailJob::dispatch($record);

                        // Setze Status auf "wird gesendet"
                        $record->update([
                            Email::status => Email::STATUS_SENT,
                        ]);
                    })
                    ->successNotificationTitle('E-Mail wird gesendet')
                    ->successNotification(
                        \Filament\Notifications\Notification::make()
                            ->success()
                            ->title('E-Mail in Warteschlange')
                            ->body('Die E-Mail wurde zur Versendung in die Warteschlange eingereiht.')
                    ),

                Action::make('resend')
                    ->label('Erneut senden')
                    ->icon('heroicon-o-arrow-path')
                    ->color('warning')
                    ->visible(fn (Email $record) => $record->status === Email::STATUS_FAILED)
                    ->requiresConfirmation()
                    ->modalHeading('E-Mail erneut senden')
                    ->modalDescription('Diese E-Mail ist zuvor fehlgeschlagen. Möchten Sie sie erneut versenden?')
                    ->modalSubmitActionLabel('Ja, erneut senden')
                    ->action(function (Email $record) {
                        // Zurücksetzen auf Draft und Fehler löschen
                        $record->update([
                            Email::status => Email::STATUS_DRAFT,
                            Email::error_message => null,
                        ]);

                        // Erneut dispatchen
                        SendEmailJob::dispatch($record);

                        $record->update([
                            Email::status => Email::STATUS_SENT,
                        ]);
                    })
                    ->successNotificationTitle('E-Mail wird erneut gesendet'),

                Action::make('duplicate')
                    ->label('Duplizieren')
                    ->icon('heroicon-o-document-duplicate')
                    ->color('gray')
                    ->action(function (Email $record) {
                        $newEmail = $record->replicate();
                        $newEmail->status = Email::STATUS_DRAFT;
                        $newEmail->sent_at = null;
                        $newEmail->read_at = null;
                        $newEmail->message_id = null;
                        $newEmail->save();
                    })
                    ->successNotificationTitle('E-Mail wurde dupliziert')
                    ->after(fn () => $this->dispatch('refreshTable')),
            ])
            ->toolbarActions([
                DeleteBulkAction::make(),
            ])
            ->defaultSort('created_at', 'desc')
            ->emptyStateHeading('Keine gesendeten E-Mails')
            ->emptyStateDescription('Erstellen Sie Ihre erste E-Mail')
            ->emptyStateActions([
                Action::make('create')
                    ->label('E-Mail erstellen')
                    ->icon('heroicon-o-plus')
                    ->url(EmailResource::getCreateUrl())
                    ->button(),
            ]);
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('compose')
                ->label('Neue E-Mail')
                ->icon('heroicon-o-pencil-square')
                ->color('primary')
                ->url(EmailResource::getCreateUrl())
                ->button(),

            Action::make('refresh')
                ->label('Aktualisieren')
                ->icon('heroicon-o-arrow-path')
                ->color('gray')
                ->action(fn () => $this->dispatch('refreshTable')),
        ];
    }
}
