<?php

namespace App\Filament\Admin\Pages;

use App\Models\Document;
use App\Models\EmailTemplate;
use App\Settings\GeneralSettings;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Storage;
use UnitEnum;

class GeneralSettingsPage extends Page implements HasForms
{
    use InteractsWithForms;

    protected static BackedEnum|string|null $navigationIcon = 'heroicon-o-cog-6-tooth';

    protected static ?string $navigationLabel = 'Einstellungen';

    protected static ?string $title = 'Allgemeine Einstellungen';

    protected static string|null|UnitEnum $navigationGroup = 'System';

    protected static ?int $navigationSort = 20;

    protected string $view = 'filament.admin.pages.general-settings-page';

    public ?array $data = [];

    public function mount(): void
    {
        /** @var GeneralSettings $settings */
        $settings = app(GeneralSettings::class);

        $this->form->fill([
            GeneralSettings::default_payment_method      => $settings->default_payment_method,
            GeneralSettings::default_rental_duration     => $settings->default_rental_duration,
            GeneralSettings::max_fields_per_customer     => $settings->max_fields_per_customer,
            GeneralSettings::field_width_cm              => $settings->field_width_cm,
            GeneralSettings::field_height_cm             => $settings->field_height_cm,
            GeneralSettings::field_gap_cm                => $settings->field_gap_cm,
            GeneralSettings::email_notifications_enabled => $settings->email_notifications_enabled,
            GeneralSettings::default_email_template_id   => $settings->default_email_template_id,
            GeneralSettings::required_document_ids       => $settings->required_document_ids ?? [],
            GeneralSettings::sepa_mandate_text           => $settings->sepa_mandate_text,
            GeneralSettings::data_confirmation_text      => $settings->data_confirmation_text,
            GeneralSettings::inquiry_overview_info_text  => $settings->inquiry_overview_info_text,
            GeneralSettings::logo_path                   => $settings->logo_path ? [$settings->logo_path] : [],
        ]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make('Allgemeine Einstellungen')
                    ->description('Grundeinstellungen für das gesamte System')
                    ->schema([
                        Select::make(GeneralSettings::default_payment_method)
                            ->label('Standard Zahlungsmethode')
                            ->options([
                                GeneralSettings::PAYMENT_METHOD_SEPA => 'Überweisung (SEPA)',
                                GeneralSettings::PAYMENT_METHOD_DONATION => 'Spendenquittung nur auf Anfrage',
                            ])
                            ->required()
                            ->helperText('Die voreingestellte Zahlungsmethode für neue Kunden')
                            ->native(false),

                        TextInput::make(GeneralSettings::default_rental_duration)
                            ->label('Standard Mietdauer (Monate)')
                            ->numeric()
                            ->required()
                            ->minValue(1)
                            ->maxValue(12)
                            ->default(1)
                            ->suffix('Monat(e)')
                            ->helperText('Voreingestellte Mietdauer in Monaten'),

                        TextInput::make(GeneralSettings::max_fields_per_customer)
                            ->label('Maximale Anzahl Felder pro Kunde')
                            ->numeric()
                            ->required()
                            ->minValue(1)
                            ->maxValue(100)
                            ->default(10)
                            ->suffix('Feld(er)')
                            ->helperText('Wie viele Felder kann ein Kunde maximal mieten'),
                    ])
                    ->columns(2),

                Section::make('Feld-Abmessungen')
                    ->description('Physische Maße der einzelnen Kacheln auf der Fördertafel (alle Angaben in cm)')
                    ->schema([
                        TextInput::make(GeneralSettings::field_width_cm)
                            ->label('Kachelbreite (cm)')
                            ->numeric()
                            ->required()
                            ->minValue(0.1)
                            ->step(0.1)
                            ->default(8.9)
                            ->suffix('cm')
                            ->helperText('Breite einer einzelnen Kachel in Zentimetern'),

                        TextInput::make(GeneralSettings::field_height_cm)
                            ->label('Kachelhöhe (cm)')
                            ->numeric()
                            ->required()
                            ->minValue(0.1)
                            ->step(0.1)
                            ->default(5.1)
                            ->suffix('cm')
                            ->helperText('Höhe einer einzelnen Kachel in Zentimetern'),

                        TextInput::make(GeneralSettings::field_gap_cm)
                            ->label('Abstand zwischen Kacheln (cm)')
                            ->numeric()
                            ->required()
                            ->minValue(0)
                            ->step(0.1)
                            ->default(1.2)
                            ->suffix('cm')
                            ->helperText('Abstand (Gap) zwischen zwei benachbarten Kacheln in Zentimetern'),
                    ])
                    ->columns(3),

                Section::make('E-Mail Einstellungen')
                    ->description('Konfiguration für E-Mail-Benachrichtigungen')
                    ->schema([
                        Toggle::make(GeneralSettings::email_notifications_enabled)
                            ->label('E-Mail Benachrichtigungen aktivieren')
                            ->helperText('Aktiviert oder deaktiviert das Versenden von E-Mail-Benachrichtigungen')
                            ->default(true)
                            ->inline(false),

                        Select::make(GeneralSettings::default_email_template_id)
                            ->label('Standard E-Mail Vorlage')
                            ->options(EmailTemplate::where('is_active', true)->pluck(EmailTemplate::name, 'id'))
                            ->searchable()
                            ->preload()
                            ->nullable()
                            ->helperText('Die Vorlage, die standardmäßig für E-Mails verwendet wird')
                            ->native(false),

                        FileUpload::make(GeneralSettings::logo_path)
                            ->label('Organisations-Logo')
                            ->helperText('Das Logo wird im Header aller ausgehenden E-Mails angezeigt. Empfohlen: PNG mit transparentem Hintergrund, min. 200 px Breite.')
                            ->image()
                            ->imagePreviewHeight('80')
                            ->disk('public')
                            ->directory('settings/logo')
                            ->visibility('public')
                            ->maxSize(2048)
                            ->acceptedFileTypes(['image/png', 'image/jpeg', 'image/svg+xml', 'image/webp'])
                            ->deleteUploadedFileUsing(function ($file) {
                                Storage::disk('public')->delete($file);
                            })
                            ->columnSpanFull(),
                    ])
                    ->columns(1),

                Section::make('Anfrage Einstellungen')
                    ->description('Einstellungen für den Anfrage-Prozess (Kundenportal)')
                    ->schema([
                        Select::make(GeneralSettings::required_document_ids)
                            ->label('Pflichtdokumente')
                            ->multiple()
                            ->options(
                                Document::query()
                                    ->where(Document::is_current_version, true)
                                    ->orderBy(Document::title)
                                    ->get()
                                    ->mapWithKeys(fn (Document $doc) => [
                                        $doc->id => $doc->getTypeLabel() . ' – ' . $doc->title,
                                    ])
                            )
                            ->searchable()
                            ->preload()
                            ->nullable()
                            ->helperText('Dokumente, die Kunden bei der Anfrage akzeptieren müssen (z. B. AGB, Beitragsordnung, Datenschutzerklärung). Es werden nur aktuelle Versionen angezeigt.')
                            ->native(false),

                        Textarea::make(GeneralSettings::sepa_mandate_text)
                            ->label('SEPA-Mandat Hinweistext')
                            ->rows(6)
                            ->helperText('Dieser Text wird dem Kunden beim Akzeptieren des SEPA-Mandats angezeigt.')
                            ->placeholder('SEPA-Mandat Hinweistext eingeben...')
                            ->columnSpanFull(),

                        Textarea::make(GeneralSettings::data_confirmation_text)
                            ->label('Datenkorrektheit-Bestätigungstext')
                            ->rows(3)
                            ->helperText('Dieser Text wird dem Kunden als Bestätigung der Datenkorrektheit im letzten Schritt der Anfrage angezeigt.')
                            ->placeholder('Bestätigungstext eingeben...')
                            ->columnSpanFull(),

                        Textarea::make(GeneralSettings::inquiry_overview_info_text)
                            ->label('Hinweistext Übersichtsseite')
                            ->rows(3)
                            ->helperText('Dieser Hinweistext wird im Übersichts-Schritt (Schritt 6) vor dem Absenden angezeigt.')
                            ->placeholder('Hinweistext eingeben...')
                            ->columnSpanFull(),
                    ])
                    ->columns(1),

                Action::make('save')
                    ->label('Einstellungen speichern')
                    ->button()
                    ->color('primary')
                    ->icon('heroicon-o-check')
                    ->submit('save'),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        $data = $this->form->getState();

        // FileUpload gibt ein Array zurück – wir brauchen nur den ersten Eintrag (einzelne Datei)
        if (isset($data['logo_path']) && is_array($data['logo_path'])) {
            $data['logo_path'] = !empty($data['logo_path'])
                ? array_values($data['logo_path'])[0]
                : null;
        }

        /** @var GeneralSettings $settings */
        $settings = app(GeneralSettings::class);

        // Altes Logo löschen wenn ein neues hochgeladen wurde
        if (
            isset($data['logo_path'])
            && $settings->logo_path
            && $data['logo_path'] !== $settings->logo_path
        ) {
            Storage::disk('public')->delete($settings->logo_path);
        }

        $settings->default_payment_method      = $data[GeneralSettings::default_payment_method];
        $settings->default_rental_duration     = (int) $data[GeneralSettings::default_rental_duration];
        $settings->max_fields_per_customer     = (int) $data[GeneralSettings::max_fields_per_customer];
        $settings->field_width_cm              = (float) $data[GeneralSettings::field_width_cm];
        $settings->field_height_cm             = (float) $data[GeneralSettings::field_height_cm];
        $settings->field_gap_cm                = (float) $data[GeneralSettings::field_gap_cm];
        $settings->email_notifications_enabled = (bool) $data[GeneralSettings::email_notifications_enabled];
        $settings->default_email_template_id   = isset($data[GeneralSettings::default_email_template_id]) ? (int) $data[GeneralSettings::default_email_template_id] : null;
        $settings->required_document_ids       = $data[GeneralSettings::required_document_ids] ?? [];
        $settings->sepa_mandate_text           = $data[GeneralSettings::sepa_mandate_text] ?? null;
        $settings->data_confirmation_text      = $data[GeneralSettings::data_confirmation_text] ?? null;
        $settings->inquiry_overview_info_text  = $data[GeneralSettings::inquiry_overview_info_text] ?? null;
        $settings->logo_path                   = $data[GeneralSettings::logo_path] ?? null;
        $settings->save();

        Notification::make()
            ->success()
            ->title('Einstellungen gespeichert')
            ->body('Die Einstellungen wurden erfolgreich gespeichert.')
            ->send();
    }

    protected function getFormActions(): array
    {
        return [
            Action::make('save')
                ->label('Speichern')
                ->submit('save')
                ->icon('heroicon-o-check'),
        ];
    }
}
