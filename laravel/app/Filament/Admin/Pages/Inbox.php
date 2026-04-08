<?php

namespace App\Filament\Admin\Pages;

use App\Filament\Admin\Resources\Emails\EmailResource;
use App\Models\Email;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Pages\Page;
use Filament\Support\Enums\IconSize;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use UnitEnum;

class Inbox extends Page implements HasTable
{
    use InteractsWithTable;

    protected static string|null|BackedEnum $navigationIcon = Heroicon::Inbox;

    protected string $view = 'filament.admin.pages.inbox';

    protected static ?string $navigationLabel = 'Posteingang';

    protected static ?string $title = 'Posteingang';

    protected static string|null|UnitEnum $navigationGroup = 'Kommunikation';

    protected static ?int $navigationSort = 3;
    protected static bool $shouldRegisterNavigation = false;

    public static function getNavigationBadge(): ?string
    {
        $unreadCount = Email::inbound()->unread()->count();
        return $unreadCount > 0 ? (string) $unreadCount : null;
    }

    public static function getNavigationBadgeColor(): string|array|null
    {
        return 'danger';
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(Email::query()->inbound())
            ->columns([
                IconColumn::make(Email::read_at)
                    ->label('')
                    ->boolean()
                    ->trueIcon('heroicon-o-envelope-open')
                    ->falseIcon('heroicon-o-envelope')
                    ->trueColor('gray')
                    ->falseColor('primary')
                    ->tooltip(fn ($record) => $record->read_at ? 'Gelesen' : 'Ungelesen')
                    ->size(IconSize::Large),

                TextColumn::make(Email::from_email)
                    ->label('Von')
                    ->searchable()
                    ->sortable()
                    ->formatStateUsing(fn ($record) => $record->from_name
                        ?: $record->from_email)
                    ->description(fn ($record) => $record->from_name
                        ? $record->from_email
                        : null)
                    ->weight(fn ($record) => $record->isRead() ? 'normal' : 'bold'),

                TextColumn::make(Email::subject)
                    ->label('Betreff')
                    ->searchable()
                    ->sortable()
                    ->limit(60)
                    ->tooltip(fn ($record) => $record->subject)
                    ->weight(fn ($record) => $record->isRead() ? 'normal' : 'bold')
                    ->description(fn ($record) => strip_tags(substr($record->body_html ?? $record->body_text, 0, 100)) . '...'),

                TextColumn::make('customer.name')
                    ->label('Kunde')
                    ->searchable()
                    ->sortable()
                    ->placeholder('Unbekannt')
                    ->toggleable(),

                TextColumn::make(Email::received_at)
                    ->label('Empfangen')
                    ->dateTime('d.m.Y H:i')
                    ->sortable()
                    ->description(fn ($record) => $record->received_at?->diffForHumans()),
            ])
            ->filters([
                TernaryFilter::make(Email::read_at)
                    ->label('Status')
                    ->placeholder('Alle')
                    ->trueLabel('Nur gelesene')
                    ->falseLabel('Nur ungelesene')
                    ->queries(
                        true: fn ($query) => $query->whereNotNull(Email::read_at),
                        false: fn ($query) => $query->whereNull(Email::read_at),
                    )
                    ->default(false),

                SelectFilter::make(Email::customer_id)
                    ->label('Kunde')
                    ->relationship('customer', 'name')
                    ->searchable()
                    ->preload()
                    ->native(false),
            ])
            ->recordActions([
                Action::make('view')
                    ->label('Ansehen')
                    ->icon('heroicon-o-eye')
                    ->color('info')
                    ->url(fn (Email $record): string => EmailResource::getViewUrl($record))
                    ->openUrlInNewTab(false)
                    ->after(function (Email $record) {
                        if (!$record->isRead()) {
                            $record->markAsRead();
                        }
                    }),

                Action::make('markAsRead')
                    ->label('Als gelesen markieren')
                    ->icon('heroicon-o-envelope-open')
                    ->color('success')
                    ->visible(fn (Email $record) => !$record->isRead())
                    ->action(function (Email $record) {
                        $record->markAsRead();
                    })
                    ->successNotificationTitle('Als gelesen markiert'),

                Action::make('reply')
                    ->label('Antworten')
                    ->icon('heroicon-o-arrow-uturn-left')
                    ->color('primary')
                    ->url(fn (Email $record): string => EmailResource::getCreateUrl([
                        'to_email' => $record->from_email,
                        'to_name' => $record->from_name,
                        'subject' => 'Re: ' . $record->subject,
                        'in_reply_to' => $record->message_id,
                    ])),
            ])
            ->toolbarActions([
                BulkAction::make('markAsRead')
                    ->label('Als gelesen markieren')
                    ->icon('heroicon-o-envelope-open')
                    ->color('success')
                    ->action(function ($records) {
                        foreach ($records as $record) {
                            $record->markAsRead();
                        }
                    })
                    ->deselectRecordsAfterCompletion()
                    ->successNotificationTitle('Alle ausgewählten E-Mails wurden als gelesen markiert'),

                DeleteBulkAction::make(),
            ])
            ->defaultSort('received_at', 'desc')
            ->poll('30s');
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('refresh')
                ->label('Aktualisieren')
                ->icon('heroicon-o-arrow-path')
                ->color('gray')
                ->action(fn () => $this->dispatch('refreshTable')),
        ];
    }
}
