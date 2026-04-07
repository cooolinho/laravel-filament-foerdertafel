<?php

namespace App\Filament\Admin\Pages;

use App\Models\Document;
use App\Models\EmailTemplate;
use App\Models\Setting;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use UnitEnum;

class SettingsPage extends Page implements HasForms
{
    use InteractsWithForms;

    protected static BackedEnum|string|null $navigationIcon = 'heroicon-o-cog-6-tooth';

    protected static ?string $navigationLabel = 'Einstellungen';

    protected static ?string $title = 'Einstellungen';

    protected static string|null|UnitEnum $navigationGroup = 'System';

    protected static ?int $navigationSort = 20;

    protected string $view = 'filament.admin.pages.settings-page';

    public ?array $data = [];

    public function mount(): void
    {
        $settings = Setting::current();

        if ($settings) {
            $this->form->fill([
                Setting::default_payment_method     => $settings->default_payment_method,
                Setting::default_rental_duration    => $settings->default_rental_duration,
                Setting::max_fields_per_customer    => $settings->max_fields_per_customer,
                Setting::field_width_cm             => $settings->field_width_cm,
                Setting::field_height_cm            => $settings->field_height_cm,
                Setting::field_gap_cm               => $settings->field_gap_cm,
                Setting::email_notifications_enabled => $settings->email_notifications_enabled,
                Setting::default_email_template_id  => $settings->default_email_template_id,
                Setting::terms_conditions_document_id => $settings->terms_conditions_document_id,
            ]);
        }
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make('Allgemeine Einstellungen')
                    ->description('Grundeinstellungen für das gesamte System')
                    ->schema([
                        Select::make(Setting::default_payment_method)
                            ->label('Standard Zahlungsmethode')
                            ->options(Setting::getPaymentMethods())
                            ->required()
                            ->helperText('Die voreingestellte Zahlungsmethode für neue Kunden')
                            ->native(false),

                        TextInput::make(Setting::default_rental_duration)
                            ->label('Standard Mietdauer (Monate)')
                            ->numeric()
                            ->required()
                            ->minValue(1)
                            ->maxValue(12)
                            ->default(1)
                            ->suffix('Monat(e)')
                            ->helperText('Voreingestellte Mietdauer in Monaten'),

                        TextInput::make(Setting::max_fields_per_customer)
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
                        TextInput::make(Setting::field_width_cm)
                            ->label('Kachelbreite (cm)')
                            ->numeric()
                            ->required()
                            ->minValue(0.1)
                            ->step(0.1)
                            ->default(8.9)
                            ->suffix('cm')
                            ->helperText('Breite einer einzelnen Kachel in Zentimetern'),

                        TextInput::make(Setting::field_height_cm)
                            ->label('Kachelhöhe (cm)')
                            ->numeric()
                            ->required()
                            ->minValue(0.1)
                            ->step(0.1)
                            ->default(5.1)
                            ->suffix('cm')
                            ->helperText('Höhe einer einzelnen Kachel in Zentimetern'),

                        TextInput::make(Setting::field_gap_cm)
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
                        Toggle::make(Setting::email_notifications_enabled)
                            ->label('E-Mail Benachrichtigungen aktivieren')
                            ->helperText('Aktiviert oder deaktiviert das Versenden von E-Mail-Benachrichtigungen')
                            ->default(true)
                            ->inline(false),

                        Select::make(Setting::default_email_template_id)
                            ->label('Standard E-Mail Vorlage')
                            ->options(EmailTemplate::where('is_active', true)->pluck(EmailTemplate::name, 'id'))
                            ->searchable()
                            ->preload()
                            ->nullable()
                            ->helperText('Die Vorlage, die standardmäßig für E-Mails verwendet wird')
                            ->native(false),
                    ])
                    ->columns(1),

                Section::make('Anfrage Einstellungen')
                    ->description('Einstellungen für den Anfrage-Prozess (Kundenportal)')
                    ->schema([
                        Select::make(Setting::terms_conditions_document_id)
                            ->label('AGB Dokument')
                            ->options(
                                Document::query()
                                    ->where(Document::type, Document::TYPE_TERMS_CONDITIONS)
                                    ->where(Document::is_current_version, true)
                                    ->orderBy(Document::title)
                                    ->pluck(Document::title, 'id')
                            )
                            ->searchable()
                            ->preload()
                            ->nullable()
                            ->helperText('Das AGB-Dokument, welches Kunden bei der Anfrage akzeptieren müssen. Nur Dokumente vom Typ „AGB" werden angezeigt.')
                            ->native(false),
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
        $settings = Setting::current();

        if ($settings) {
            $settings->update($data);
        } else {
            Setting::create($data);
        }

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
