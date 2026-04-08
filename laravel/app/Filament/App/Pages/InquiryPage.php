<?php

namespace App\Filament\App\Pages;

use App\Events\InquiryCreated;
use App\Models\Board;
use App\Models\Document;
use App\Models\Field;
use App\Models\Inquiry;
use App\Models\Rental;
use App\Rules\FieldsFormRectangle;
use App\Rules\MaxFieldsCount;
use App\Settings\GeneralSettings;
use BackedEnum;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Infolists\Components\TextEntry;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Enums\Width;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\HtmlString;

class InquiryPage extends Page implements HasForms
{
    use InteractsWithForms;

    protected string $view = 'filament.app.pages.inquiry-page';

    protected static string|null|BackedEnum $navigationIcon = 'heroicon-o-clipboard-document-list';

    protected static ?string $navigationLabel = 'Anfrage stellen';

    protected static ?int $navigationSort = 2;

    protected static ?string $title = 'Anfrage stellen';
    protected static ?string $slug = 'anfrage-stellen';

    public ?Board $board = null;

    /** Aktueller Schritt (1–6) */
    public int $currentStep = 1;

    // Formular-Daten je Schritt
    public array $contactData    = [];
    public array $rentalData     = [];
    public array $paymentData    = [];
    public array $attachmentsData = [];
    public array $overviewData    = [];

    public array $selectedFields = [];

    public function mount(): void
    {
        $boardId = request()->query('board');

        if ($boardId) {
            $this->board = Board::with([
                'fields.rentals' => function ($query) {
                    $query->where(Rental::status, Rental::STATUS_ACTIVE)
                        ->where(Rental::start_date, '<=', now())
                        ->where(Rental::end_date, '>=', now());
                },
                'fields.rentals.customer'
            ])->find($boardId);
        } else {
            $this->board = Board::with([
                'fields.rentals' => function ($query) {
                    $query->where(Rental::status, Rental::STATUS_ACTIVE)
                        ->where(Rental::start_date, '<=', now())
                        ->where(Rental::end_date, '>=', now());
                },
                'fields.rentals.customer'
            ])->first();
        }

        // Alle Teilformulare initialisieren
        $this->contactForm->fill();
        $this->rentalForm->fill();
        $this->paymentForm->fill();
        $this->attachmentsForm->fill();
        $this->overviewForm->fill();
    }

    // ── Schritt-Titel ────────────────────────────────────────────────────────

    public function getStepTitles(): array
    {
        return [
            1 => 'Felder',
            2 => 'Kontaktdaten',
            3 => 'Mietdetails',
            4 => 'Zahlung',
            5 => 'Anhänge & Dokumente',
            6 => 'Übersicht',
        ];
    }

    // ── Schritt 2: Kontaktdaten ──────────────────────────────────────────────

    public function contactForm(Schema $schema): Schema
    {
        return $schema
            ->schema([
                TextInput::make('customer_name')
                    ->label('Ihr Name')
                    ->required()
                    ->maxLength(255)
                    ->placeholder('Max Mustermann'),

                TextInput::make('customer_email')
                    ->label('Ihre E-Mail')
                    ->email()
                    ->required()
                    ->maxLength(255)
                    ->placeholder('max@beispiel.de'),

                TextInput::make('customer_phone')
                    ->label('Ihre Telefonnummer')
                    ->tel()
                    ->maxLength(255)
                    ->placeholder('+49 123 456789')
                    ->columnSpanFull(),

                Checkbox::make('is_company')
                    ->label('Anfrage als Unternehmen')
                    ->reactive()
                    ->columnSpanFull(),

                TextInput::make('company_name')
                    ->label('Unternehmensname')
                    ->maxLength(255)
                    ->placeholder('Musterfirma GmbH')
                    ->visible(fn ($get) => (bool) $get('is_company'))
                    ->required(fn ($get) => (bool) $get('is_company'))
                    ->columnSpanFull(),

                TextInput::make('street')
                    ->label('Straße')
                    ->required()
                    ->maxLength(255)
                    ->placeholder('Musterstraße'),

                TextInput::make('street_nr')
                    ->label('Hausnummer')
                    ->required()
                    ->maxLength(20)
                    ->placeholder('12a'),

                TextInput::make('zip')
                    ->label('PLZ')
                    ->required()
                    ->maxLength(10)
                    ->placeholder('12345'),

                TextInput::make('city')
                    ->label('Stadt')
                    ->required()
                    ->maxLength(255)
                    ->placeholder('Berlin'),
            ])
            ->columns(2)
            ->statePath('contactData');
    }

    // ── Schritt 3: Mietdetails ───────────────────────────────────────────────

    public function rentalForm(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Select::make('start_month')
                    ->label('Start-Monat')
                    ->options($this->getAvailableMonths())
                    ->required()
                    ->native(false)
                    ->reactive()
                    ->afterStateUpdated(fn () => $this->dispatch('dates-updated'))
                    ->helperText('Wählen Sie den Monat, in dem Ihre Miete beginnen soll. Die Miete startet immer am 1. des Monats.')
                    ->placeholder('Monat auswählen')
                    ->columnSpanFull(),

                TextEntry::make('rental_info')
                    ->label('Mietdauer')
                    ->state(function ($get) {
                        $startMonth = $get('start_month');
                        if (!$startMonth) {
                            return 'Bitte wählen Sie zunächst einen Start-Monat aus.';
                        }

                        $duration = app(\App\Settings\GeneralSettings::class)->default_rental_duration ?? 1;
                        $startDate = \Carbon\Carbon::parse($startMonth);
                        $endDate = $startDate->copy()->addMonths($duration)->subDay();

                        return sprintf(
                            'Ihre Miete läuft vom %s bis %s (%d %s).',
                            $startDate->format('d.m.Y'),
                            $endDate->format('d.m.Y'),
                            $duration,
                            $duration === 1 ? 'Monat' : 'Monate'
                        );
                    })
                    ->columnSpanFull(),

                Textarea::make('message')
                    ->label('Gewünschter Text auf der Kachel (optional)')
                    ->rows(4)
                    ->placeholder('Geben Sie hier den Text ein, der auf Ihrer Kachel erscheinen soll. Zum Beispiel Ihren Namen, einen Gruß oder ein Motto. Lassen Sie das Feld leer, wenn Sie keinen Text wünschen.')
                    ->columnSpanFull(),
            ])
            ->statePath('rentalData');
    }

    // ── Schritt 4: Zahlungsinformationen ─────────────────────────────────────

    public function paymentForm(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Radio::make('payment_method')
                    ->label('Bevorzugte Zahlungsmethode')
                    ->options(GeneralSettings::paymentMethods())
                    ->default(GeneralSettings::PAYMENT_METHOD_SEPA)
                    ->required()
                    ->reactive()
                    ->helperText(fn ($get) => $get('payment_method') === GeneralSettings::PAYMENT_METHOD_SEPA
                        ? 'TSV von 1908 Großenkneten e.V. akzeptiert nur SEPA Zahlungen.'
                        : null)
                    ->columnSpanFull(),

                TextInput::make('account_holder')
                    ->label('Kontoinhaber')
                    ->required()
                    ->maxLength(255)
                    ->visible(fn ($get) => $get('payment_method') === GeneralSettings::PAYMENT_METHOD_SEPA)
                    ->columnSpanFull(),

                TextInput::make('iban')
                    ->label('IBAN')
                    ->required()
                    ->maxLength(34)
                    ->visible(fn ($get) => $get('payment_method') === GeneralSettings::PAYMENT_METHOD_SEPA)
                    ->columnSpanFull(),

                TextInput::make('bic')
                    ->label('BIC')
                    ->maxLength(11)
                    ->visible(fn ($get) => $get('payment_method') === GeneralSettings::PAYMENT_METHOD_SEPA),

                TextInput::make('bank_name')
                    ->label('Bankname')
                    ->maxLength(255)
                    ->visible(fn ($get) => $get('payment_method') === GeneralSettings::PAYMENT_METHOD_SEPA)
                    ->helperText(new HtmlString(
                        '<p class="text-sm text-primary-600">BIC oder Bankname sind unbekannt? Du kannst diese Felder freilassen und wir ermitteln sie aus der IBAN.</p>'
                    )),

                Checkbox::make('sepa_mandate_accepted')
                    ->label('SEPA-Mandat akzeptieren')
                    ->required()
                    ->rules(['accepted'])
                    ->validationMessages([
                        'accepted' => 'Sie müssen das SEPA-Mandat akzeptieren, um eine Anfrage stellen zu können.',
                    ])
                    ->helperText(app(GeneralSettings::class)->sepa_mandate_text)
                    ->visible(fn ($get) => $get('payment_method') === GeneralSettings::PAYMENT_METHOD_SEPA)
                    ->columnSpanFull(),

                Section::make('Rechnungsanschrift')
                    ->schema([
                        Checkbox::make('billing_use_postal_address')
                            ->label('Postadresse verwenden')
                            ->default(true)
                            ->reactive()
                            ->columnSpanFull(),

                        TextInput::make('billing_street')
                            ->label('Straße und Hausnummer')
                            ->placeholder('Torstraße 177')
                            ->maxLength(255)
                            ->required(fn ($get) => !(bool) $get('billing_use_postal_address'))
                            ->visible(fn ($get) => !(bool) $get('billing_use_postal_address')),

                        TextInput::make('billing_address2')
                            ->label('Adresszeile 2')
                            ->placeholder('C/O, Firma, Gebäude, ...')
                            ->maxLength(255)
                            ->visible(fn ($get) => !(bool) $get('billing_use_postal_address')),

                        TextInput::make('billing_zip')
                            ->label('PLZ')
                            ->maxLength(10)
                            ->required(fn ($get) => !(bool) $get('billing_use_postal_address'))
                            ->visible(fn ($get) => !(bool) $get('billing_use_postal_address')),

                        TextInput::make('billing_city')
                            ->label('Stadt')
                            ->maxLength(255)
                            ->required(fn ($get) => !(bool) $get('billing_use_postal_address'))
                            ->visible(fn ($get) => !(bool) $get('billing_use_postal_address')),

                        Select::make('billing_country')
                            ->label('Rechnungsland')
                            ->options([
                                'Deutschland' => 'Deutschland',
                                'Österreich'  => 'Österreich',
                                'Schweiz'     => 'Schweiz',
                            ])
                            ->default('Deutschland')
                            ->native(false)
                            ->required(fn ($get) => !(bool) $get('billing_use_postal_address'))
                            ->visible(fn ($get) => !(bool) $get('billing_use_postal_address')),
                    ])
                    ->columns(2)
                    ->columnSpanFull(),
            ])
            ->columns(2)
            ->statePath('paymentData');
    }

    // ── Schritt 5: Anhänge & AGB ─────────────────────────────────────────────

    public function attachmentsForm(Schema $schema): Schema
    {
        return $schema
            ->schema([
                FileUpload::make('attachments')
                    ->label('Anhänge (optional)')
                    ->helperText('Laden Sie Dokumente oder Bilder hoch, die Ihre Anfrage ergänzen (z. B. Design-Vorlage für Ihre Kachel). PDF, JPG, PNG – max. 10 MB pro Datei.')
                    ->multiple()
                    ->disk('local')
                    ->directory('inquiry/tmp')
                    ->acceptedFileTypes(['application/pdf', 'image/jpeg', 'image/png', 'image/gif', 'image/webp'])
                    ->maxSize(10240)
                    ->reorderable()
                    ->appendFiles()
                    ->columnSpanFull(),
            ])
            ->statePath('attachmentsData');
    }

    // ── Schritt 6: Übersicht – Checkboxen & Absenden ────────────────────────

    public function overviewForm(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Checkbox::make('accept_terms')
                    ->label($this->buildTermsLabel())
                    ->required()
                    ->rules(['accepted'])
                    ->validationMessages([
                        'accepted' => 'Sie müssen alle Pflichtdokumente akzeptieren.',
                    ])
                    ->columnSpanFull(),

                Checkbox::make('confirm_data_correctness')
                    ->label(app(GeneralSettings::class)->data_confirmation_text ?? "Durch Angabe meiner Daten erkläre ich meine Daten als korrekt.")
                    ->required()
                    ->rules(['accepted'])
                    ->validationMessages([
                        'accepted' => 'Bitte bestätigen Sie die Korrektheit Ihrer Angaben.',
                    ])
                    ->columnSpanFull(),
            ])
            ->statePath('overviewData');
    }

    public function nextStep(): void
    {
        if ($this->currentStep === 1) {
            // Feldauswahl validieren
            if (empty($this->selectedFields)) {
                Notification::make()
                    ->title('Felder auswählen')
                    ->body('Bitte wählen Sie mindestens ein Feld aus.')
                    ->warning()
                    ->send();
                return;
            }

            $validator = Validator::make(
                ['selected_fields' => $this->selectedFields],
                [
                    'selected_fields' => [
                        'required', 'array', 'min:1',
                        new MaxFieldsCount(app(\App\Settings\GeneralSettings::class)->max_fields_per_customer ?? 4),
                        new FieldsFormRectangle(),
                    ],
                ],
                [
                    'selected_fields.required' => 'Bitte wählen Sie mindestens ein Feld aus.',
                    'selected_fields.min'      => 'Bitte wählen Sie mindestens ein Feld aus.',
                ]
            );

            if ($validator->fails()) {
                Notification::make()
                    ->title('Ungültige Feld-Auswahl')
                    ->body($validator->errors()->first('selected_fields'))
                    ->danger()
                    ->send();
                return;
            }
        } elseif ($this->currentStep === 2) {
            $this->contactForm->getState();     // wirft ValidationException bei Fehler
        } elseif ($this->currentStep === 3) {
            $this->rentalForm->getState();
        } elseif ($this->currentStep === 4) {
            $this->paymentForm->getState();
        } elseif ($this->currentStep === 5) {
            $this->attachmentsForm->getState();
        }

        if ($this->currentStep < 6) {
            $this->currentStep++;
        }
    }

    public function previousStep(): void
    {
        if ($this->currentStep > 1) {
            $this->currentStep--;
        }
    }

    public function toggleField(int $fieldId): void
    {
        $maxFields = (int) app(\App\Settings\GeneralSettings::class)->max_fields_per_customer ?? 4;

        if (in_array($fieldId, $this->selectedFields)) {
            // Remove field
            $this->selectedFields = array_values(array_diff($this->selectedFields, [$fieldId]));
        } else {
            // Check if max fields limit would be exceeded
            if (count($this->selectedFields) >= $maxFields) {
                Notification::make()
                    ->title('Maximale Anzahl erreicht')
                    ->body("Sie können maximal {$maxFields} Felder auswählen.")
                    ->warning()
                    ->send();
                return;
            }

            // Add field
            $this->selectedFields[] = $fieldId;
        }

        $this->dispatch('fields-updated');
    }

    /**
     * Get available months for selection (starting 14 days from now)
     */
    protected function getAvailableMonths(): array
    {
        $months = [];

        // Frühestes Start-Datum ist in 14 Tagen
        $earliestDate = now()->addDays(14);

        // Wenn wir nicht am Monatsanfang sind, nehmen wir den nächsten Monat
        if ($earliestDate->day > 1) {
            $startMonth = $earliestDate->copy()->addMonth()->startOfMonth();
        } else {
            $startMonth = $earliestDate->copy()->startOfMonth();
        }

        // Generiere die nächsten 12 Monate als Optionen
        for ($i = 0; $i < 12; $i++) {
            $month = $startMonth->copy()->addMonths($i);
            $key = $month->format('Y-m-01'); // Immer der 1. des Monats
            $label = $month->translatedFormat('F Y'); // z.B. "März 2026"
            $months[$key] = $label;
        }

        return $months;
    }

    public function submit(): void
    {
        // Alle Formulardaten mit finaler Validierung abrufen
        $contactData     = $this->contactForm->getState();
        $rentalData      = $this->rentalForm->getState();
        $paymentData     = $this->paymentForm->getState();
        $attachmentsData = $this->attachmentsForm->getState();
        $this->overviewForm->getState(); // Checkboxen validieren

        $billingDifferent = !(bool) ($paymentData['billing_use_postal_address'] ?? true);

        try {
            $inquiry = DB::transaction(function () use ($contactData, $rentalData, $paymentData, $billingDifferent) {
                $startDate = \Carbon\Carbon::parse($rentalData['start_month']);
                $duration  = app(\App\Settings\GeneralSettings::class)->default_rental_duration ?? 1;
                $endDate   = $startDate->copy()->addMonths($duration)->subDay();

                $inquiry = Inquiry::create([
                    Inquiry::board_id             => $this->board->id,
                    Inquiry::customer_name        => $contactData['customer_name'],
                    Inquiry::customer_email       => $contactData['customer_email'],
                    Inquiry::customer_phone       => $contactData['customer_phone'] ?? null,
                    Inquiry::is_company           => (bool) ($contactData['is_company'] ?? false),
                    Inquiry::company_name         => ($contactData['is_company'] ?? false) ? ($contactData['company_name'] ?? null) : null,
                    Inquiry::street               => $contactData['street'],
                    Inquiry::street_nr            => $contactData['street_nr'],
                    Inquiry::zip                  => $contactData['zip'],
                    Inquiry::city                 => $contactData['city'],
                    Inquiry::start_date           => $startDate,
                    Inquiry::end_date             => $endDate,
                    Inquiry::rental_months        => $duration,
                    Inquiry::requested_fields     => $this->selectedFields,
                    Inquiry::status               => Inquiry::STATUS_PENDING,
                    Inquiry::message              => $rentalData['message'] ?? null,
                    // Zahlungsdaten
                    Inquiry::payment_method       => $paymentData['payment_method'] ?? GeneralSettings::PAYMENT_METHOD_SEPA,
                    Inquiry::account_holder       => $paymentData['account_holder'] ?? null,
                    Inquiry::iban                 => $paymentData['iban'] ?? null,
                    Inquiry::bic                  => $paymentData['bic'] ?? null,
                    Inquiry::bank_name            => $paymentData['bank_name'] ?? null,
                    Inquiry::sepa_mandate_accepted => (bool) ($paymentData['sepa_mandate_accepted'] ?? false),
                    // Rechnungsanschrift
                    Inquiry::billing_use_postal_address => !$billingDifferent,
                    Inquiry::billing_street       => $billingDifferent ? ($paymentData['billing_street'] ?? null) : null,
                    Inquiry::billing_address2     => $billingDifferent ? ($paymentData['billing_address2'] ?? null) : null,
                    Inquiry::billing_zip          => $billingDifferent ? ($paymentData['billing_zip'] ?? null) : null,
                    Inquiry::billing_city         => $billingDifferent ? ($paymentData['billing_city'] ?? null) : null,
                    Inquiry::billing_country      => $billingDifferent ? ($paymentData['billing_country'] ?? 'Deutschland') : null,
                ]);

                $inquiry->fields()->attach($this->selectedFields);

                return $inquiry;
            });

            // Dateien von tmp in inquiry/{id}/ verschieben
            $tmpFiles = $attachmentsData['attachments'] ?? [];
            if (!empty($tmpFiles)) {
                $finalPaths = [];
                foreach ($tmpFiles as $tmpPath) {
                    $filename  = basename($tmpPath);
                    $finalPath = "inquiry/{$inquiry->id}/{$filename}";
                    Storage::disk('local')->move($tmpPath, $finalPath);
                    $finalPaths[] = $finalPath;
                }
                $inquiry->update([Inquiry::attachments => $finalPaths]);
            }

            // Event NACH der Transaktion und nach dem Datei-Verschieben feuern,
            // damit inquiry->fields vollständig gespeichert sind wenn der
            // Listener (via Queue + afterCommit) die E-Mail erstellt.
//            event(new InquiryCreated($inquiry));
            InquiryCreated::dispatch($inquiry);

            session(['inquiry_complete' => $inquiry->id]);
            $this->redirect(route('filament.app.pages.anfrage-erfolgreich-gesendet'));

        } catch (\Exception $e) {
            Notification::make()
                ->title('Fehler')
                ->body('Beim Senden der Anfrage ist ein Fehler aufgetreten.')
                ->danger()
                ->send();
        }
    }

    // ── Roh-Preis-Methoden ────────────────────────────────────────────────────

    public function getTotalPricePerMonth(): float
    {
        if (empty($this->selectedFields)) {
            return 0.0;
        }

        return (float) Field::whereIn('id', $this->selectedFields)
            ->sum(Field::price_per_month);
    }

    /**
     * Basispreis = Mietkosten aller Felder × Monate + Einrichtungskosten.
     * Bei inklusiver MwSt. entspricht dieser Wert dem Bruttobetrag.
     * Bei exklusiver MwSt. entspricht er dem Nettobetrag.
     */
    private function getBasePrice(): float
    {
        $pricePerMonth = $this->getTotalPricePerMonth();
        $setupCost     = (float) app(GeneralSettings::class)->initial_setup_cost;
        $months        = max(1, (int) app(GeneralSettings::class)->default_rental_duration);

        return round(($pricePerMonth * $months) + $setupCost, 2);
    }

    /** Konfigurierter MwSt.-Satz in Prozent (0 = keine MwSt.) */
    public function getVatRate(): float
    {
        return (float) (app(GeneralSettings::class)->invoice_vat_rate ?? 0);
    }

    /** true = MwSt. im Preis enthalten | false = MwSt. wird aufgeschlagen */
    public function isVatInclusive(): bool
    {
        return app(GeneralSettings::class)->isVatInclusive();
    }

    /**
     * Nettobetrag (ohne MwSt.).
     * Inklusiv: Brutto / (1 + Satz/100)
     * Exklusiv: Basispreis
     */
    public function getNetTotal(): float
    {
        $base = $this->getBasePrice();
        $rate = $this->getVatRate();

        if ($rate <= 0) {
            return $base;
        }

        if ($this->isVatInclusive()) {
            return round($base / (1 + $rate / 100), 2);
        }

        return $base;
    }

    /**
     * MwSt.-Betrag.
     * Inklusiv: Brutto − Netto
     * Exklusiv: Netto × Satz/100
     */
    public function getVatAmount(): float
    {
        $rate = $this->getVatRate();

        if ($rate <= 0) {
            return 0.0;
        }

        if ($this->isVatInclusive()) {
            return round($this->getBasePrice() - $this->getNetTotal(), 2);
        }

        return round($this->getNetTotal() * $rate / 100, 2);
    }

    /**
     * Bruttobetrag – der endgültig zu zahlende Betrag.
     * Inklusiv: Basispreis (MwSt. bereits enthalten)
     * Exklusiv: Netto + MwSt.
     */
    public function getGrossTotal(): float
    {
        if ($this->isVatInclusive()) {
            return $this->getBasePrice();
        }

        return round($this->getNetTotal() + $this->getVatAmount(), 2);
    }

    /** Rückwärtskompatibilität – gibt den Bruttobetrag zurück */
    public function getTotalPrice(): float
    {
        return $this->getGrossTotal();
    }

    // ── Formatierungs-Hilfsmethoden ───────────────────────────────────────────

    private function formatMoney(float $value): string
    {
        return number_format($value, 2, ',', '.') . ' €';
    }

    private function formatCm(float $value): string
    {
        return number_format($value, 1, ',', '.') . ' cm';
    }

    // ── Formatierte Preis-Methoden ────────────────────────────────────────────

    public function getFormattedPricePerMonth(): string
    {
        return $this->formatMoney($this->getTotalPricePerMonth());
    }

    public function getFormattedSetupCost(): string
    {
        return $this->formatMoney((float) app(GeneralSettings::class)->initial_setup_cost);
    }

    public function getFormattedNetTotal(): string
    {
        return $this->formatMoney($this->getNetTotal());
    }

    public function getFormattedVatRate(): string
    {
        return number_format($this->getVatRate(), 0, ',', '.') . ' %';
    }

    public function getFormattedVatAmount(): string
    {
        return $this->formatMoney($this->getVatAmount());
    }

    public function getFormattedGrossTotal(): string
    {
        return $this->formatMoney($this->getGrossTotal());
    }

    /**
     * Label für die MwSt.-Zeile im Template.
     * Inklusiv: "inkl. 19 % MwSt.:"
     * Exklusiv: "zzgl. 19 % MwSt.:"
     */
    public function getVatLabel(): string
    {
        $prefix = $this->isVatInclusive() ? 'inkl.' : 'zzgl.';

        return "{$prefix} {$this->getFormattedVatRate()} MwSt.:";
    }

    // ── Formatierte Settings-Hilfsmethoden ────────────────────────────────────

    public function getMaxFieldsPerCustomer(): int
    {
        return app(GeneralSettings::class)->getMaxFieldsPerCustomer();
    }

    public function getMaxSelectionRows(): int
    {
        return app(GeneralSettings::class)->getMaxSelectionRows();
    }

    public function getMaxSelectionCols(): int
    {
        return app(GeneralSettings::class)->getMaxSelectionCols();
    }

    public function getRentalDuration(): int
    {
        return max(1, (int) app(GeneralSettings::class)->default_rental_duration);
    }

    public function getFormattedFieldWidthCm(): string
    {
        return $this->formatCm((float) app(GeneralSettings::class)->field_width_cm);
    }

    public function getFormattedFieldHeightCm(): string
    {
        return $this->formatCm((float) app(GeneralSettings::class)->field_height_cm);
    }

    public function getFormattedFieldGapCm(): string
    {
        return $this->formatCm((float) app(GeneralSettings::class)->field_gap_cm);
    }

    public function getInquiryInfoText(): ?string
    {
        return app(GeneralSettings::class)->inquiry_overview_info_text;
    }

    /**
     * Gibt die physischen Abmessungen der aktuellen Feldauswahl zurück.
     * Berechnet Breite/Höhe in cm basierend auf den Einstellungen.
     *
     * @return array{rows: int, cols: int, width_cm: float, height_cm: float}|null
     */
    public function getSelectedFieldDimensions(): ?array
    {
        if (empty($this->selectedFields)) {
            return null;
        }

        $fields = Field::whereIn('id', $this->selectedFields)->get();

        if ($fields->isEmpty()) {
            return null;
        }

        $minRow = $fields->min(Field::row);
        $maxRow = $fields->max(Field::row);
        $minCol = $fields->min(Field::column);
        $maxCol = $fields->max(Field::column);

        // Berücksichtige die field height/width (multi-cell fields)
        $maxRowEnd = $fields->map(fn($f) => $f->{Field::row} + $f->{Field::height} - 1)->max();
        $maxColEnd = $fields->map(fn($f) => $f->{Field::column} + $f->{Field::width} - 1)->max();

        $selectionRows = $maxRowEnd - $minRow + 1;
        $selectionCols = $maxColEnd - $minCol + 1;

        $widthCm  = app(GeneralSettings::class)->calculatePhysicalWidth($selectionCols);
        $heightCm = app(GeneralSettings::class)->calculatePhysicalHeight($selectionRows);

        return [
            'rows'       => $selectionRows,
            'cols'       => $selectionCols,
            'width_cm'   => $widthCm,
            'height_cm'  => $heightCm,
            'width_fmt'  => $this->formatCm($widthCm),
            'height_fmt' => $this->formatCm($heightCm),
        ];
    }

    public function getFieldsGrid(): array
    {
        if (!$this->board) {
            return [];
        }

        $fields = $this->board->fields;

        // Create grid structure with null values
        $grid = [];
        for ($row = 1; $row <= $this->board->{Board::rows}; $row++) {
            $grid[$row] = [];
            for ($col = 1; $col <= $this->board->{Board::columns}; $col++) {
                $grid[$row][$col] = null;
            }
        }

        // Place fields in grid, marking occupied positions
        foreach ($fields as $field) {
            $fieldRow = $field->{Field::row};
            $fieldCol = $field->{Field::column};
            $fieldWidth = $field->{Field::width};
            $fieldHeight = $field->{Field::height};

            // Mark the top-left position with the field object
            if (array_key_exists($fieldRow, $grid) && array_key_exists($fieldCol, $grid[$fieldRow])) {
                $grid[$fieldRow][$fieldCol] = $field;
            }

            // Mark all other occupied positions as 'occupied'
            for ($r = $fieldRow; $r < $fieldRow + $fieldHeight; $r++) {
                for ($c = $fieldCol; $c < $fieldCol + $fieldWidth; $c++) {
                    if ($r === $fieldRow && $c === $fieldCol) {
                        continue;
                    }

                    if (array_key_exists($r, $grid) && array_key_exists($c, $grid[$r])) {
                        $grid[$r][$c] = 'occupied';
                    }
                }
            }
        }

        return $grid;
    }

    /**
     * Get the active rental for a field
     */
    public function getActiveRental(Field $field): ?Rental
    {
        $rental = $field->rentals()
            ->whereIn(Rental::status, [Rental::STATUS_ACTIVE, Rental::STATUS_PAID])

            // erstmal nicht prüfen da ansonsten doppel buchungen vorkommen können. anschließend muss dann mit dem kunden
            // kommuniziert werden das das feld nur bis verfügbar ist, bzw. in einem bestimmten zeitraum zur verfügung steht
//            ->where(Rental::start_date, '<=', now())
//            ->where(Rental::end_date, '>=', now())

            ->with('customer')
            ->first();

        return $rental;
    }

    /**
     * Get all fields directly (not in grid structure)
     */
    public function getFields()
    {
        if (!$this->board) {
            return collect();
        }

        return $this->board->fields;
    }

    public function getMaxContentWidth(): Width
    {
        return Width::Full;
    }

    /**
     * Erstellt das Label für die Pflichtdokumente-Checkbox.
     * Enthält Links zu allen konfigurierten Dokumenten.
     */
    protected function buildTermsLabel(): string|HtmlString
    {
        $documents = app(GeneralSettings::class)->getRequiredDocuments();

        if ($documents->isEmpty()) {
            return 'Ich akzeptiere die Allgemeinen Geschäftsbedingungen.';
        }

        $links = $documents->map(function (Document $document) {
            $url = route('documents.show', ['document' => $document->{Document::file_name}]);
            return '<a href="' . e($url) . '" target="_blank" rel="noopener noreferrer" '
                . 'class="underline font-medium text-primary-600 hover:text-primary-500">'
                . e($document->title)
                . '</a>';
        })->all();

        $count = count($links);

        if ($count === 1) {
            $linkString = $links[0];
        } elseif ($count === 2) {
            $linkString = $links[0] . ' und ' . $links[1];
        } else {
            $last = array_pop($links);
            $linkString = implode(', ', $links) . ' und ' . $last;
        }

        return new HtmlString('Ich habe ' . $linkString . ' gelesen und akzeptiere sie.');
    }
}
