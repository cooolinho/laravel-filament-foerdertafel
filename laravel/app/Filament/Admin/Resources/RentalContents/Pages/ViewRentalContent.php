<?php

namespace App\Filament\Admin\Resources\RentalContents\Pages;

use App\Filament\Admin\Resources\RentalContents\RentalContentResource;
use App\Models\RentalContent;
use Filament\Actions\Action;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ViewRentalContent extends ViewRecord
{
    protected static string $resource = RentalContentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('approve')
                ->label('Freigeben')
                ->icon('heroicon-o-check-circle')
                ->color('success')
                ->visible(fn (RentalContent $record) => $record->hasPendingReview())
                ->requiresConfirmation()
                ->modalHeading('Inhalt freigeben')
                ->modalDescription('Der Inhalt wird freigegeben und als veröffentlicht markiert. Der Kunde kann danach wieder Änderungen einreichen.')
                ->modalSubmitActionLabel('Freigeben')
                ->action(function (RentalContent $record) {
                    $record->is_published = true;
                    $record->approve();

                    Notification::make()
                        ->success()
                        ->title('Inhalt freigegeben')
                        ->body('Der Inhalt wurde erfolgreich freigegeben und ist nun öffentlich sichtbar.')
                        ->send();
                }),

            Action::make('reject')
                ->label('Ablehnen')
                ->icon('heroicon-o-x-circle')
                ->color('danger')
                ->visible(fn (RentalContent $record) => $record->hasPendingReview())
                ->requiresConfirmation()
                ->modalHeading('Inhalt ablehnen')
                ->modalDescription('Der Inhalt wird abgelehnt. Der Kunde kann danach neue Änderungen einreichen. Der Inhalt bleibt unveröffentlicht.')
                ->modalSubmitActionLabel('Ablehnen')
                ->action(function (RentalContent $record) {
                    $record->reject();

                    Notification::make()
                        ->warning()
                        ->title('Inhalt abgelehnt')
                        ->body('Der Inhalt wurde abgelehnt. Der Kunde kann nun erneut Änderungen einreichen.')
                        ->send();
                }),
        ];
    }

    public function infolist(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Prüfungsstatus')
                    ->schema([
                        TextEntry::make(RentalContent::needs_review)
                            ->label('Status')
                            ->badge()
                            ->formatStateUsing(fn (bool $state) => $state ? 'Prüfung ausstehend' : 'Geprüft')
                            ->color(fn (bool $state) => $state ? 'warning' : 'success'),

                        TextEntry::make(RentalContent::review_requested_at)
                            ->label('Änderung eingereicht am')
                            ->dateTime('d.m.Y H:i')
                            ->placeholder('—'),
                    ])
                    ->columns(2),

                Section::make('Kundeninformationen')
                    ->schema([
                        TextEntry::make('rental.customer.name')
                            ->label('Kunde'),

                        TextEntry::make('rental.customer.company_name')
                            ->label('Firma')
                            ->placeholder('—'),

                        TextEntry::make('rental.customer.email')
                            ->label('E-Mail')
                            ->copyable(),

                        TextEntry::make(RentalContent::access_code)
                            ->label('Zugangscode')
                            ->fontFamily('mono')
                            ->copyable(),
                    ])
                    ->columns(2),

                Section::make('Inhalt (zur Prüfung)')
                    ->schema([
                        TextEntry::make(RentalContent::title)
                            ->label('Titel')
                            ->placeholder('—'),

                        TextEntry::make(RentalContent::description)
                            ->label('Beschreibung')
                            ->placeholder('—')
                            ->columnSpanFull(),

                        TextEntry::make(RentalContent::website_url)
                            ->label('Website')
                            ->url(fn ($record) => $record->website_url)
                            ->openUrlInNewTab()
                            ->placeholder('—'),

                        TextEntry::make(RentalContent::contact_email)
                            ->label('Kontakt E-Mail')
                            ->placeholder('—'),

                        TextEntry::make(RentalContent::contact_phone)
                            ->label('Telefonnummer')
                            ->placeholder('—'),

                        ImageEntry::make(RentalContent::company_logo)
                            ->label('Firmenlogo')
                            ->disk('public')
                            ->visible(fn (RentalContent $record) => $record->company_logo !== null),
                    ])
                    ->columns(3),
            ]);
    }
}

