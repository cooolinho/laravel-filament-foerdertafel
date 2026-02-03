<?php

namespace App\Filament\App\Pages;

use App\Events\InquiryCreated;
use App\Models\Board;
use App\Models\Field;
use App\Models\Inquiry;
use App\Models\Rental;
use App\Models\Setting;
use App\Rules\FieldsFormRectangle;
use App\Rules\MaxFieldsCount;
use BackedEnum;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Schema;
use Filament\Support\Enums\Width;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class InquiryPage extends Page implements HasForms
{
    use InteractsWithForms;

    protected string $view = 'filament.app.pages.inquiry-page';

    protected static string|null|BackedEnum $navigationIcon = 'heroicon-o-clipboard-document-list';

    protected static ?string $navigationLabel = 'Anfrage stellen';

    protected static ?int $navigationSort = 2;

    protected static ?string $title = 'Anfrage stellen';

    public ?Board $board = null;

    public ?array $data = [];

    public array $selectedFields = [];

    public function mount(): void
    {
        // Lade das Board mit allen Feldern
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

        $this->form->fill();
    }

    public function form(Schema $schema): Schema
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
                    ->placeholder('+49 123 456789'),

                Select::make('start_month')
                    ->label('Start-Monat')
                    ->options($this->getAvailableMonths())
                    ->required()
                    ->native(false)
                    ->reactive()
                    ->afterStateUpdated(fn () => $this->dispatch('dates-updated'))
                    ->helperText('Wählen Sie den Monat, in dem Ihre Miete beginnen soll. Die Miete startet immer am 1. des Monats.')
                    ->placeholder('Monat auswählen'),

                Placeholder::make('rental_info')
                    ->label('Mietdauer')
                    ->content(function ($get) {
                        $startMonth = $get('start_month');
                        if (!$startMonth) {
                            return 'Bitte wählen Sie zunächst einen Start-Monat aus.';
                        }

                        $duration = Setting::get(Setting::default_rental_duration, 1);
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
                    ->label('Nachricht (optional)')
                    ->rows(3)
                    ->placeholder('Ihre Nachricht an uns...')
                    ->columnSpanFull(),
            ])
            ->statePath('data');
    }

    public function toggleField(int $fieldId): void
    {
        if (in_array($fieldId, $this->selectedFields)) {
            // Remove field
            $this->selectedFields = array_values(array_diff($this->selectedFields, [$fieldId]));
        } else {
            // Check if max fields limit would be exceeded
            if (count($this->selectedFields) >= 10) {
                Notification::make()
                    ->title('Maximale Anzahl erreicht')
                    ->body('Sie können maximal 10 Felder auswählen.')
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
        // Validate form data
        $formData = $this->form->getState();

        // Validate that at least one field is selected
        if (empty($this->selectedFields)) {
            Notification::make()
                ->title('Fehler')
                ->body('Bitte wählen Sie mindestens ein Feld aus.')
                ->danger()
                ->send();
            return;
        }

        // Validate selected fields with custom rules
        $validator = Validator::make(
            ['selected_fields' => $this->selectedFields],
            [
                'selected_fields' => [
                    'required',
                    'array',
                    'min:1',
                    new MaxFieldsCount(Setting::get(Setting::max_fields_per_customer, 4)),
                    new FieldsFormRectangle(),
                ],
            ],
            [
                'selected_fields.required' => 'Bitte wählen Sie mindestens ein Feld aus.',
                'selected_fields.min' => 'Bitte wählen Sie mindestens ein Feld aus.',
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

        try {
            $inquiry = DB::transaction(function () use ($formData) {
                // Calculate start_date and end_date from start_month
                $startDate = \Carbon\Carbon::parse($formData['start_month']); // Immer der 1. des Monats
                $duration = Setting::get(Setting::default_rental_duration, 1);
                $endDate = $startDate->copy()->addMonths($duration)->subDay(); // Letzter Tag des Miet-Zeitraums

                $inquiry = Inquiry::create([
                    Inquiry::board_id => $this->board->id,
                    Inquiry::customer_name => $formData['customer_name'],
                    Inquiry::customer_email => $formData['customer_email'],
                    Inquiry::customer_phone => $formData['customer_phone'] ?? null,
                    Inquiry::start_date => $startDate,
                    Inquiry::end_date => $endDate,
                    Inquiry::requested_fields => $this->selectedFields,
                    Inquiry::status => Inquiry::STATUS_PENDING,
                    Inquiry::message => $formData['message'] ?? null,
                ]);

                // Attach fields via pivot table
                $inquiry->fields()->attach($this->selectedFields);

                return $inquiry;
            });

            // Dispatch event to reserve fields
            InquiryCreated::dispatch($inquiry);

            // Set session variable and redirect to confirmation page
            session(['inquiry_complete' => $inquiry->id]);
            $this->redirect(route('filament.app.pages.inquiry-complete-page'));

        } catch (\Exception $e) {
            Notification::make()
                ->title('Fehler')
                ->body('Beim Senden der Anfrage ist ein Fehler aufgetreten.')
                ->danger()
                ->send();
        }
    }

    public function getTotalPricePerMonth(): float
    {
        if (empty($this->selectedFields)) {
            return 0;
        }

        return Field::whereIn('id', $this->selectedFields)
            ->sum(Field::price_per_month);
    }

    public function getTotalPrice(): float
    {
        $pricePerMonth = $this->getTotalPricePerMonth();

        if (!isset($this->data['start_month'])) {
            return $pricePerMonth;
        }

        try {
            // Get rental duration from settings
            $months = Setting::get(Setting::default_rental_duration, 1);
            $months = max(1, $months); // Minimum 1 month

            return $pricePerMonth * $months;
        } catch (\Exception $e) {
            return $pricePerMonth;
        }
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
            ->where(Rental::status, Rental::STATUS_ACTIVE)
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
}
