<?php

namespace App\Filament\Admin\Resources\Rentals\Actions;

use App\Events\RentalPaid;
use App\Models\Rental;
use App\Models\RentalContent;
use Filament\Actions\Action;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Section;
use Illuminate\Support\Facades\Log;

class RentalActions
{
    /**
     * Action: Als bezahlt markieren und Zugangscode senden
     */
    public static function markAsPaid(): Action
    {
        return Action::make('markAsPaid')
            ->label('Als bezahlt markieren')
            ->icon('heroicon-o-check-circle')
            ->color('success')
            ->visible(fn (Rental $record) => !$record->isPaid())
            ->requiresConfirmation()
            ->modalHeading('Miete als bezahlt markieren')
            ->modalDescription('Dies markiert die Miete als bezahlt und sendet dem Kunden den Zugangscode per E-Mail.')
            ->modalSubmitActionLabel('Als bezahlt markieren')
            ->schema([
                DateTimePicker::make('paid_at')
                    ->label('Bezahlt am')
                    ->default(now())
                    ->required()
                    ->native(false),

                Checkbox::make('send_email')
                    ->label('Zugangscode per E-Mail senden')
                    ->default(true)
                    ->helperText('Sendet automatisch eine E-Mail mit dem Zugangscode an den Kunden.'),
            ])
            ->action(function (Rental $record, array $data) {
                $record->paid_at = $data['paid_at'];
                $record->status = Rental::STATUS_PAID;
                $record->save();

                // Erstelle oder hole RentalContent (Zugangscode wird automatisch generiert)
                $rentalContent = RentalContent::firstOrCreate(
                    [RentalContent::rental_id => $record->id],
                    [RentalContent::is_private_person => $record->customer->isPrivatePerson()]
                );

                // Event auslösen wenn Email gewünscht
                if ($data['send_email']) {
                    event(new RentalPaid($record));
                }

                Notification::make()
                    ->success()
                    ->title('Miete als bezahlt markiert')
                    ->body("Zugangscode: {$rentalContent->access_code}")
                    ->persistent()
                    ->send();
            });
    }

    /**
     * Action: Zugangscode anzeigen/kopieren
     */
    public static function viewAccessCode(): Action
    {
        return Action::make('viewAccessCode')
            ->label('Zugangscode anzeigen')
            ->icon('heroicon-o-key')
            ->color('info')
            ->visible(fn (Rental $record) => $record->content !== null)
            ->modalHeading('Zugangscode')
            ->modalSubmitAction(false)
            ->modalCancelActionLabel('Schließen')
            ->modalContent(function (Rental $record) {
                $content = $record->content;

                return view('filament.modals.access-code', [
                    'accessCode' => $content->access_code,
                    'accessUrl' => route('rental.content.access', ['code' => $content->access_code]),
                    'lastAccessed' => $content->last_accessed_at?->format('d.m.Y H:i'),
                    'isPublished' => $content->is_published,
                ]);
            });
    }

    /**
     * Action: Zugangscode neu generieren
     */
    public static function regenerateAccessCode(): Action
    {
        return Action::make('regenerateAccessCode')
            ->label('Zugangscode neu generieren')
            ->icon('heroicon-o-arrow-path')
            ->color('warning')
            ->visible(fn (Rental $record) => $record->content !== null)
            ->requiresConfirmation()
            ->modalHeading('Zugangscode neu generieren')
            ->modalDescription('Achtung: Der alte Zugangscode wird ungültig! Der Kunde muss den neuen Code erhalten.')
            ->modalSubmitActionLabel('Neu generieren')
            ->action(function (Rental $record) {
                $content = $record->content;
                $oldCode = $content->access_code;
                $content->access_code = RentalContent::generateUniqueAccessCode();
                $content->save();

                Notification::make()
                    ->success()
                    ->title('Zugangscode neu generiert')
                    ->body("Alter Code: {$oldCode}\nNeuer Code: {$content->access_code}")
                    ->persistent()
                    ->send();
            });
    }

    /**
     * Action: Content verwalten
     */
    public static function manageContent(): Action
    {
        return Action::make('manageContent')
            ->label('Content verwalten')
            ->icon('heroicon-o-document-text')
            ->color('primary')
            ->visible(fn (Rental $record) => $record->content !== null)
            ->modalHeading('Content verwalten')
            ->fillForm(function (Rental $record): array {
                $content = $record->content;
                return [
                    'title' => $content->title,
                    'description' => $content->description,
                    'website_url' => $content->website_url,
                    'contact_email' => $content->contact_email,
                    'contact_phone' => $content->contact_phone,
                    'is_published' => $content->is_published,
                    'company_logo' => $content->company_logo,
                ];
            })
            ->form([
                Section::make('Grundinformationen')
                    ->schema([
                        TextInput::make('title')
                            ->label('Titel')
                            ->maxLength(255)
                            ->placeholder('z.B. Firmenname oder Überschrift'),

                        Textarea::make('description')
                            ->label('Beschreibung')
                            ->rows(5)
                            ->maxLength(2000)
                            ->placeholder('Beschreibung des Angebots...'),
                    ]),

                Section::make('Kontaktinformationen')
                    ->schema([
                        TextInput::make('website_url')
                            ->label('Website')
                            ->url()
                            ->maxLength(255)
                            ->placeholder('https://www.example.com'),

                        TextInput::make('contact_email')
                            ->label('Kontakt E-Mail')
                            ->email()
                            ->maxLength(255),

                        TextInput::make('contact_phone')
                            ->label('Telefonnummer')
                            ->tel()
                            ->maxLength(50)
                            ->placeholder('+49 123 456789'),
                    ])
                    ->columns(3),

                Section::make('Logo & Veröffentlichung')
                    ->schema([
                        FileUpload::make('company_logo')
                            ->label('Firmenlogo')
                            ->image()
                            ->maxSize(2048)
                            ->directory('rental-logos')
                            ->disk('public')
                            ->visible(fn (Rental $record) => $record->content && $record->content->canUploadLogo())
                            ->helperText('Maximale Größe: 2MB. Erlaubte Formate: JPG, PNG, GIF, SVG'),

                        Toggle::make('is_published')
                            ->label('Veröffentlicht')
                            ->helperText('Macht die Inhalte öffentlich sichtbar'),
                    ])
                    ->columns(2),
            ])
            ->action(function (Rental $record, array $data) {
                $content = $record->content;
                $content->update($data);

                Notification::make()
                    ->success()
                    ->title('Content erfolgreich aktualisiert')
                    ->send();
            });
    }

    /**
     * Action: Zugangscode per E-Mail versenden
     */
    public static function resendAccessCode(): Action
    {
        return Action::make('resendAccessCode')
            ->label('Zugangscode erneut senden')
            ->icon('heroicon-o-paper-airplane')
            ->color('info')
            ->visible(fn (Rental $record) => $record->content !== null)
            ->requiresConfirmation()
            ->modalHeading('Zugangscode erneut senden')
            ->modalDescription('Sendet dem Kunden den Zugangscode erneut per E-Mail.')
            ->modalSubmitActionLabel('E-Mail senden')
            ->action(function (Rental $record) {
                try {
                    event(new RentalPaid($record));

                    Notification::make()
                        ->success()
                        ->title('E-Mail versendet')
                        ->body('Der Zugangscode wurde erfolgreich an den Kunden gesendet.')
                        ->send();
                } catch (\Exception $e) {
                    Log::error('Fehler beim Versenden des Zugangscodes: ' . $e->getMessage());

                    Notification::make()
                        ->danger()
                        ->title('Fehler beim Versenden')
                        ->body('Die E-Mail konnte nicht versendet werden. Bitte prüfen Sie die Logs.')
                        ->send();
                }
            });
    }

    /**
     * Action: Content löschen
     */
    public static function deleteContent(): Action
    {
        return Action::make('deleteContent')
            ->label('Content löschen')
            ->icon('heroicon-o-trash')
            ->color('danger')
            ->visible(fn (Rental $record) => $record->content !== null)
            ->requiresConfirmation()
            ->modalHeading('Content löschen')
            ->modalDescription('Achtung: Dies löscht alle Inhalte und der Zugangscode wird ungültig!')
            ->modalSubmitActionLabel('Ja, löschen')
            ->action(function (Rental $record) {
                $content = $record->content;

                // Logo löschen falls vorhanden
                if ($content->company_logo) {
                    \Storage::disk('public')->delete($content->company_logo);
                }

                $content->delete();

                Notification::make()
                    ->success()
                    ->title('Content gelöscht')
                    ->body('Der Content und Zugangscode wurden erfolgreich gelöscht.')
                    ->send();
            });
    }

    /**
     * Action: Content initialisieren (wenn noch nicht vorhanden)
     */
    public static function initializeContent(): Action
    {
        return Action::make('initializeContent')
            ->label('Content initialisieren')
            ->icon('heroicon-o-plus-circle')
            ->color('success')
            ->visible(fn (Rental $record) => $record->content === null && $record->isPaid())
            ->requiresConfirmation()
            ->modalHeading('Content initialisieren')
            ->modalDescription('Erstellt den Content-Bereich mit Zugangscode für diese Miete.')
            ->modalSubmitActionLabel('Initialisieren')
            ->action(function (Rental $record) {
                $rentalContent = RentalContent::create([
                    RentalContent::rental_id => $record->id,
                    RentalContent::is_private_person => $record->customer->isPrivatePerson(),
                ]);

                Notification::make()
                    ->success()
                    ->title('Content initialisiert')
                    ->body("Zugangscode: {$rentalContent->access_code}")
                    ->persistent()
                    ->send();
            });
    }

    /**
     * Action: Zum Kundenportal öffnen
     */
    public static function openCustomerPortal(): Action
    {
        return Action::make('openCustomerPortal')
            ->label('Kundenportal öffnen')
            ->icon('heroicon-o-arrow-top-right-on-square')
            ->color('gray')
            ->visible(fn (Rental $record) => $record->content !== null)
            ->url(fn (Rental $record) => route('rental.content.manage', ['code' => $record->content->access_code]))
            ->openUrlInNewTab();
    }

    /**
     * Action: Content-Status anzeigen
     */
    public static function viewContentStatus(): Action
    {
        return Action::make('viewContentStatus')
            ->label('Content-Status')
            ->icon('heroicon-o-information-circle')
            ->color('info')
            ->visible(fn (Rental $record) => $record->content !== null)
            ->modalHeading('Content-Status')
            ->modalSubmitAction(false)
            ->modalCancelActionLabel('Schließen')
            ->modalContent(function (Rental $record) {
                $content = $record->content;

                return view('filament.modals.content-status', [
                    'content' => $content,
                    'rental' => $record,
                ]);
            });
    }

    /**
     * Action: Content freigeben (nach Kundenänderung)
     */
    public static function approveContent(): Action
    {
        return Action::make('approveContent')
            ->label('Inhalt freigeben')
            ->icon('heroicon-o-check-circle')
            ->color('success')
            ->visible(fn (Rental $record) => $record->content !== null && $record->content->hasPendingReview())
            ->requiresConfirmation()
            ->modalHeading('Inhalt freigeben')
            ->modalDescription('Der Inhalt wird freigegeben und als veröffentlicht markiert. Der Kunde kann danach wieder Änderungen einreichen.')
            ->modalSubmitActionLabel('Freigeben')
            ->action(function (Rental $record) {
                $content = $record->content;
                $content->is_published = true;
                $content->approve();

                Notification::make()
                    ->success()
                    ->title('Inhalt freigegeben')
                    ->body('Der Inhalt wurde erfolgreich freigegeben und ist nun öffentlich sichtbar.')
                    ->send();
            });
    }

    /**
     * Action: Content ablehnen (nach Kundenänderung)
     */
    public static function rejectContent(): Action
    {
        return Action::make('rejectContent')
            ->label('Inhalt ablehnen')
            ->icon('heroicon-o-x-circle')
            ->color('danger')
            ->visible(fn (Rental $record) => $record->content !== null && $record->content->hasPendingReview())
            ->requiresConfirmation()
            ->modalHeading('Inhalt ablehnen')
            ->modalDescription('Der Inhalt wird abgelehnt. Der Kunde kann danach neue Änderungen einreichen. Der Inhalt bleibt unveröffentlicht.')
            ->modalSubmitActionLabel('Ablehnen')
            ->action(function (Rental $record) {
                $record->content->reject();

                Notification::make()
                    ->warning()
                    ->title('Inhalt abgelehnt')
                    ->body('Der Inhalt wurde abgelehnt. Der Kunde kann nun erneut Änderungen einreichen.')
                    ->send();
            });
    }
}
