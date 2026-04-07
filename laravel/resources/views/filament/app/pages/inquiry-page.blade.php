<x-filament-panels::page>
    @if($this->board)
        <div class="space-y-6">

            {{-- ══ SCHRITT-INDIKATOR ══ --}}
            @php $stepTitles = $this->getStepTitles(); @endphp
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow p-4 sm:p-6">
                <div class="flex items-start">
                    @foreach($stepTitles as $step => $title)
                        <div class="flex items-center {{ $step < count($stepTitles) ? 'flex-1' : '' }}">
                            <div class="flex flex-col items-center">
                                <div class="w-9 h-9 rounded-full flex items-center justify-center text-sm font-bold transition-all duration-300
                                    @if($currentStep > $step) bg-green-500 text-white
                                    @elseif($currentStep === $step) bg-blue-600 text-white ring-4 ring-blue-100 dark:ring-blue-900
                                    @else bg-gray-100 dark:bg-gray-700 text-gray-400
                                    @endif">
                                    @if($currentStep > $step)
                                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                        </svg>
                                    @else
                                        {{ $step }}
                                    @endif
                                </div>
                                <span class="mt-1.5 text-xs font-medium text-center leading-tight hidden sm:block
                                    {{ $currentStep >= $step ? 'text-gray-800 dark:text-gray-100' : 'text-gray-400' }}">
                                    {{ $title }}
                                </span>
                            </div>
                            @if($step < count($stepTitles))
                                <div class="flex-1 h-0.5 mx-1 sm:mx-2 -mt-3 sm:-mt-5 transition-colors duration-300
                                    {{ $currentStep > $step ? 'bg-green-500' : 'bg-gray-200 dark:bg-gray-700' }}"></div>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- ══ SCHRITT 1: Felder auswählen ══ --}}
            @if($currentStep === 1)
                <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-4">
                    <div class="flex items-start gap-3">
                        <svg class="w-6 h-6 text-blue-600 dark:text-blue-400 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <div>
                            <h3 class="font-semibold text-blue-900 dark:text-blue-100">Schritt 1: Felder auswählen</h3>
                            <p class="mt-1 text-sm text-blue-800 dark:text-blue-200">
                                Klicken Sie auf <strong>grüne</strong> Felder, um sie auszuwählen (max.
                                <strong>{{ \App\Models\Setting::getMaxFieldsPerCustomer() }} Felder</strong>,
                                max. {{ \App\Models\Setting::getMaxSelectionRows() }} Zeile(n) × {{ \App\Models\Setting::getMaxSelectionCols() }} Spalte(n) – zusammenhängendes Rechteck).
                            </p>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    {{-- Board Grid --}}
                    <div class="lg:col-span-2 space-y-4">
                        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4">
                            <div class="flex flex-wrap gap-6 items-center">
                                <h3 class="text-sm font-semibold text-gray-900 dark:text-white">Legende:</h3>
                                <div class="flex items-center gap-2">
                                    <div class="w-6 h-6 rounded border-2 border-green-500 bg-green-500/20"></div>
                                    <span class="text-sm text-gray-600 dark:text-gray-400">Verfügbar</span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <div class="w-6 h-6 rounded border-2 border-blue-600 bg-blue-600"></div>
                                    <span class="text-sm text-gray-600 dark:text-gray-400">Ausgewählt</span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <div class="w-6 h-6 rounded border-2 border-yellow-500 bg-yellow-500/20"></div>
                                    <span class="text-sm text-gray-600 dark:text-gray-400">Vermietet</span>
                                </div>
                            </div>
                        </div>

                        <div class="bg-white dark:bg-gray-800 rounded-lg shadow overflow-hidden">
                            @php
                                $backgroundImage = $this->board->{\App\Models\Board::background_image};
                                $backgroundUrl   = $backgroundImage ? \Storage::url($backgroundImage) : asset('img/board-preview.jpg');
                                $offsetX = $this->board->{\App\Models\Board::grid_offset_x} ?? 0;
                                $offsetY = $this->board->{\App\Models\Board::grid_offset_y} ?? 0;
                                $gap     = $this->board->{\App\Models\Board::grid_gap} ?? 12;
                                $columns = $this->board->{\App\Models\Board::columns};
                                $rows    = $this->board->{\App\Models\Board::rows};
                                $cellWidth  = (100 - (($columns - 1) * $gap / 16)) / $columns;
                                $cellHeight = (100 - (($rows - 1) * $gap / 16)) / $rows;
                            @endphp
                            <div class="relative w-full overflow-auto"
                                 style="background-image:url('{{ $backgroundUrl }}');background-size:100% 100%;background-repeat:no-repeat;height:1400px;">
                                <div class="w-full h-full" style="position:absolute;top:{{ $offsetY }}px;left:{{ $offsetX }}px;right:{{ $offsetX }}px;bottom:{{ $offsetY }}px;">
                                    @foreach($this->getFields() as $field)
                                        @php
                                            $fId   = $field->id;
                                            $fRow  = $field->{\App\Models\Field::row};
                                            $fCol  = $field->{\App\Models\Field::column};
                                            $fW    = $field->{\App\Models\Field::width};
                                            $fH    = $field->{\App\Models\Field::height};
                                            $fSt   = $field->{\App\Models\Field::status};
                                            $fIdent= $field->getIdentifier();
                                            $isSel = in_array($fId, $this->selectedFields);
                                            $rent  = $this->getActiveRental($field);
                                            $avail = $fSt === \App\Models\Field::STATUS_AVAILABLE && !$rent;
                                            $left  = (($fCol-1) * $cellWidth) + (($fCol-1) * ($gap/16));
                                            $top   = (($fRow-1) * $cellHeight) + (($fRow-1) * ($gap/16));
                                            $w     = ($fW * $cellWidth) + (($fW-1) * ($gap/16));
                                            $h     = ($fH * $cellHeight) + (($fH-1) * ($gap/16));
                                        @endphp
                                        <div class="absolute {{ $avail ? 'cursor-pointer' : '' }}"
                                             style="left:{{ $left }}%;top:{{ $top }}%;width:{{ $w }}%;height:{{ $h }}%;"
                                             @if($avail) wire:click="toggleField({{ $fId }})" @endif>
                                            @if($rent)
                                                <div class="flex h-full p-2 border-2 border-yellow-500 bg-yellow-500/20 backdrop-blur-sm rounded-lg items-center justify-center" style="min-height:10px;">
                                                    <div class="text-xs font-bold text-white bg-black/70 px-2 py-1 rounded">{{ $rent->customer->{\App\Models\Customer::name} ?? 'N/A' }}</div>
                                                </div>
                                            @elseif($avail && $isSel)
                                                <div class="flex flex-col h-full p-2 border-2 border-blue-600 bg-blue-600/80 backdrop-blur-sm rounded-lg items-center justify-center text-white transition-all duration-300" style="min-height:10px;">
                                                    <svg class="w-8 h-8 mb-1" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                                                    <div class="text-xs font-semibold">{{ $fIdent }}</div>
                                                </div>
                                            @elseif($avail)
                                                <div class="flex flex-col h-full p-2 border-2 border-green-500 bg-green-500/20 backdrop-blur-sm hover:bg-green-500/40 rounded-lg items-center justify-center transition-all duration-300" style="min-height:10px;">
                                                    <div class="w-6 h-6 rounded border-2 border-green-500 bg-green-500/20 mb-1"></div>
                                                    <div class="text-xs font-semibold text-white">{{ $fIdent }}</div>
                                                </div>
                                            @elseif($fSt === \App\Models\Field::STATUS_RESERVED)
                                                <div class="flex h-full p-2 border-2 border-blue-500 bg-blue-500/20 backdrop-blur-sm rounded-lg items-center justify-center" style="min-height:10px;">
                                                    <div class="text-xs font-semibold text-white bg-black/50 px-2 py-1 rounded">Reserviert</div>
                                                </div>
                                            @else
                                                <div class="flex h-full p-2 border-2 border-gray-500 bg-gray-500/20 backdrop-blur-sm rounded-lg" style="min-height:10px;"></div>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Seitenleiste --}}
                    <div class="space-y-4">
                        @php $dims = $this->getSelectedFieldDimensions(); @endphp
                        @if($dims)
                            <div class="bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg p-4">
                                <h4 class="text-sm font-semibold text-green-900 dark:text-green-100 mb-2">Maße ({{ $dims['rows'] }} × {{ $dims['cols'] }} Felder)</h4>
                                <div class="grid grid-cols-2 gap-2">
                                    <div class="bg-white dark:bg-green-900/30 rounded p-2 text-center">
                                        <div class="text-xs text-green-700 dark:text-green-300">Breite</div>
                                        <div class="text-lg font-bold text-green-900 dark:text-green-100">{{ number_format($dims['width_cm'], 1, ',', '.') }} cm</div>
                                        @if($dims['cols'] > 1)
                                            <div class="text-xs text-green-600 dark:text-green-400 mt-0.5">
                                                {{ $dims['cols'] }} × {{ number_format(\App\Models\Setting::get(\App\Models\Setting::field_width_cm, 8.9), 1, ',', '.') }}
                                                + {{ $dims['cols'] - 1 }} × {{ number_format(\App\Models\Setting::get(\App\Models\Setting::field_gap_cm, 1.2), 1, ',', '.') }}
                                            </div>
                                        @endif
                                    </div>
                                    <div class="bg-white dark:bg-green-900/30 rounded p-2 text-center">
                                        <div class="text-xs text-green-700 dark:text-green-300">Höhe</div>
                                        <div class="text-lg font-bold text-green-900 dark:text-green-100">{{ number_format($dims['height_cm'], 1, ',', '.') }} cm</div>
                                        @if($dims['rows'] > 1)
                                            <div class="text-xs text-green-600 dark:text-green-400 mt-0.5">
                                                {{ $dims['rows'] }} × {{ number_format(\App\Models\Setting::get(\App\Models\Setting::field_height_cm, 5.1), 1, ',', '.') }}
                                                + {{ $dims['rows'] - 1 }} × {{ number_format(\App\Models\Setting::get(\App\Models\Setting::field_gap_cm, 1.2), 1, ',', '.') }}
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endif

                        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6 sticky top-6">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Preisübersicht</h3>
                            <div class="space-y-3">
                                <div class="flex justify-between items-center pb-3 border-b border-gray-200 dark:border-gray-700">
                                    <span class="text-sm text-gray-600 dark:text-gray-400">Ausgewählte Felder:</span>
                                    <span class="font-semibold text-gray-900 dark:text-white">{{ count($this->selectedFields) }}</span>
                                </div>
                                <div class="flex justify-between items-center pb-3 border-b border-gray-200 dark:border-gray-700">
                                    <span class="text-sm text-gray-600 dark:text-gray-400">Preis pro Monat:</span>
                                    <span class="font-semibold text-gray-900 dark:text-white">{{ number_format($this->getTotalPricePerMonth(), 2, ',', '.') }} €</span>
                                </div>
                                <div class="flex justify-between items-center pt-2">
                                    <span class="text-base font-semibold text-gray-900 dark:text-white">Gesamtpreis:</span>
                                    <span class="text-2xl font-bold text-blue-600 dark:text-blue-400">{{ number_format($this->getTotalPrice(), 2, ',', '.') }} €</span>
                                </div>
                            </div>
                            @if(count($this->selectedFields) === 0)
                                <div class="mt-4 text-sm text-amber-600 dark:text-amber-400 bg-amber-50 dark:bg-amber-900/20 p-3 rounded">
                                    Bitte wählen Sie mindestens ein Feld aus.
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="flex justify-end">
                    <button wire:click="nextStep"
                            class="inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white font-semibold py-3 px-6 rounded-lg transition-colors duration-200">
                        Weiter
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                    </button>
                </div>
            @endif

            {{-- ══ MINI-PREISLEISTE (Schritte 2–5) ══ --}}
            @if($currentStep >= 2 && $currentStep <= 5)
                <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg px-5 py-3">
                    <div class="flex flex-wrap items-center justify-between gap-3 text-sm">
                        <span class="text-blue-800 dark:text-blue-200">
                            <strong>{{ count($this->selectedFields) }}</strong> {{ count($this->selectedFields) === 1 ? 'Feld' : 'Felder' }} ausgewählt
                        </span>
                        <span class="text-blue-800 dark:text-blue-200">
                            <strong>{{ number_format($this->getTotalPricePerMonth(), 2, ',', '.') }} €</strong> / Monat
                        </span>
                        <span class="text-lg font-bold text-blue-700 dark:text-blue-300">
                            Gesamt: {{ number_format($this->getTotalPrice(), 2, ',', '.') }} €
                        </span>
                    </div>
                </div>
            @endif

            {{-- ══ SCHRITT 2: Kontaktdaten ══ --}}
            @if($currentStep === 2)
                <form wire:submit="nextStep">
                    <div class="max-w-2xl mx-auto">
                        <div class="bg-white dark:bg-gray-800 rounded-xl shadow p-6">
                            <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-6">Ihre Kontaktdaten</h2>
                            {{ $this->contactForm }}
                        </div>
                    </div>
                    @include('filament.app.pages.partials.inquiry-nav-buttons')
                </form>
            @endif

            {{-- ══ SCHRITT 3: Mietdetails ══ --}}
            @if($currentStep === 3)
                <form wire:submit="nextStep">
                    <div class="max-w-2xl mx-auto">
                        <div class="bg-white dark:bg-gray-800 rounded-xl shadow p-6">
                            <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-6">Mietdetails</h2>
                            {{ $this->rentalForm }}
                        </div>
                    </div>
                    @include('filament.app.pages.partials.inquiry-nav-buttons')
                </form>
            @endif

            {{-- ══ SCHRITT 4: Zahlungsinformationen ══ --}}
            @if($currentStep === 4)
                <form wire:submit="nextStep">
                    <div class="max-w-2xl mx-auto">
                        <div class="bg-white dark:bg-gray-800 rounded-xl shadow p-6">
                            <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-6">Zahlungsinformationen</h2>
                            {{ $this->paymentForm }}
                        </div>
                    </div>
                    @include('filament.app.pages.partials.inquiry-nav-buttons')
                </form>
            @endif

            {{-- ══ SCHRITT 5: Anhänge & AGB ══ --}}
            @if($currentStep === 5)
                <form wire:submit="nextStep">
                    <div class="max-w-2xl mx-auto">
                        <div class="bg-white dark:bg-gray-800 rounded-xl shadow p-6">
                            <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-6">Anhänge & AGB</h2>
                            {{ $this->attachmentsForm }}
                        </div>
                    </div>
                    @include('filament.app.pages.partials.inquiry-nav-buttons')
                </form>
            @endif

            {{-- ══ SCHRITT 6: Übersicht & Absenden ══ --}}
            @if($currentStep === 6)
                @php
                    $cd = $this->contactData;
                    $rd = $this->rentalData;
                    $pd = $this->paymentData;
                    $ad = $this->attachmentsData;
                    $duration  = (int) \App\Models\Setting::get(\App\Models\Setting::default_rental_duration, 1);
                    $startDate = !empty($rd['start_month']) ? \Carbon\Carbon::parse($rd['start_month']) : null;
                    $endDate   = $startDate ? $startDate->copy()->addMonths($duration)->subDay() : null;
                    $billDiff  = isset($pd['billing_use_postal_address']) && !(bool) $pd['billing_use_postal_address'];
                    $iban      = $pd['iban'] ?? '';
                    $maskedIban = strlen($iban) > 4 ? str_repeat('•', strlen($iban) - 4) . substr($iban, -4) : $iban;
                @endphp

                <div class="max-w-3xl mx-auto space-y-4">

                    {{-- Preiskarte --}}
                    <div class="bg-blue-600 text-white rounded-xl shadow p-5">
                        <div class="flex flex-wrap items-center justify-between gap-4">
                            <div>
                                <div class="text-blue-200 text-sm">Gesamtpreis ({{ $duration }} {{ $duration === 1 ? 'Monat' : 'Monate' }})</div>
                                <div class="text-3xl font-extrabold">{{ number_format($this->getTotalPrice(), 2, ',', '.') }} €</div>
                            </div>
                            <div class="flex gap-6 text-sm">
                                <div>
                                    <div class="text-blue-200">Felder</div>
                                    <div class="text-lg font-bold">{{ count($this->selectedFields) }}</div>
                                </div>
                                <div>
                                    <div class="text-blue-200">Pro Monat</div>
                                    <div class="text-lg font-bold">{{ number_format($this->getTotalPricePerMonth(), 2, ',', '.') }} €</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Maße der gewählten Felder --}}
                    @php $overviewDims = $this->getSelectedFieldDimensions(); @endphp
                    @if($overviewDims)
                        <div class="bg-white dark:bg-gray-800 rounded-xl shadow divide-y divide-gray-100 dark:divide-gray-700">
                            <div class="flex items-center justify-between px-6 py-4">
                                <h3 class="font-bold text-gray-900 dark:text-white">Maße Ihrer Auswahl</h3>
                                <span class="text-xs text-gray-500 dark:text-gray-400">{{ $overviewDims['rows'] }} × {{ $overviewDims['cols'] }} Felder</span>
                            </div>
                            <div class="px-6 py-4">
                                <div class="grid grid-cols-2 gap-4">
                                    <div class="bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg p-4 text-center">
                                        <div class="text-xs font-medium text-green-700 dark:text-green-300 mb-1">Breite</div>
                                        <div class="text-2xl font-extrabold text-green-900 dark:text-green-100">
                                            {{ number_format($overviewDims['width_cm'], 1, ',', '.') }} cm
                                        </div>
                                        @if($overviewDims['cols'] > 1)
                                            <div class="text-xs text-green-600 dark:text-green-400 mt-1">
                                                {{ $overviewDims['cols'] }} × {{ number_format(\App\Models\Setting::get(\App\Models\Setting::field_width_cm, 8.9), 1, ',', '.') }} cm
                                                + {{ $overviewDims['cols'] - 1 }} × {{ number_format(\App\Models\Setting::get(\App\Models\Setting::field_gap_cm, 1.2), 1, ',', '.') }} cm Abstand
                                            </div>
                                        @endif
                                    </div>
                                    <div class="bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg p-4 text-center">
                                        <div class="text-xs font-medium text-green-700 dark:text-green-300 mb-1">Höhe</div>
                                        <div class="text-2xl font-extrabold text-green-900 dark:text-green-100">
                                            {{ number_format($overviewDims['height_cm'], 1, ',', '.') }} cm
                                        </div>
                                        @if($overviewDims['rows'] > 1)
                                            <div class="text-xs text-green-600 dark:text-green-400 mt-1">
                                                {{ $overviewDims['rows'] }} × {{ number_format(\App\Models\Setting::get(\App\Models\Setting::field_height_cm, 5.1), 1, ',', '.') }} cm
                                                + {{ $overviewDims['rows'] - 1 }} × {{ number_format(\App\Models\Setting::get(\App\Models\Setting::field_gap_cm, 1.2), 1, ',', '.') }} cm Abstand
                                            </div>
                                        @endif
                                    </div>
                                </div>
                                <p class="mt-3 text-xs text-gray-500 dark:text-gray-400">
                                    Dies sind die physischen Abmessungen Ihrer Werbeflächenauswahl auf dem Board.
                                </p>
                            </div>
                        </div>
                    @endif

                    {{-- Kontaktdaten --}}
                    <div class="bg-white dark:bg-gray-800 rounded-xl shadow divide-y divide-gray-100 dark:divide-gray-700">
                        <div class="flex items-center justify-between px-6 py-4">
                            <h3 class="font-bold text-gray-900 dark:text-white">Kontaktdaten</h3>
                            <button type="button" wire:click="$set('currentStep',2)" class="text-xs text-blue-600 dark:text-blue-400 hover:underline">Bearbeiten</button>
                        </div>
                        <dl class="px-6 py-4 grid grid-cols-1 sm:grid-cols-2 gap-x-8 gap-y-2 text-sm">
                            @if(!empty($cd['is_company']))
                                <div><dt class="text-gray-500 inline">Unternehmen: </dt><dd class="font-medium inline">{{ $cd['company_name'] ?? '–' }}</dd></div>
                            @endif
                            <div><dt class="text-gray-500 inline">Name: </dt><dd class="font-medium inline">{{ $cd['customer_name'] ?? '–' }}</dd></div>
                            <div><dt class="text-gray-500 inline">E-Mail: </dt><dd class="font-medium inline">{{ $cd['customer_email'] ?? '–' }}</dd></div>
                            @if(!empty($cd['customer_phone']))
                                <div><dt class="text-gray-500 inline">Telefon: </dt><dd class="font-medium inline">{{ $cd['customer_phone'] }}</dd></div>
                            @endif
                            <div class="sm:col-span-2"><dt class="text-gray-500 inline">Adresse: </dt>
                                <dd class="font-medium inline">{{ ($cd['street'] ?? '') }} {{ ($cd['street_nr'] ?? '') }}, {{ ($cd['zip'] ?? '') }} {{ ($cd['city'] ?? '') }}</dd>
                            </div>
                        </dl>
                    </div>

                    {{-- Mietdetails --}}
                    <div class="bg-white dark:bg-gray-800 rounded-xl shadow divide-y divide-gray-100 dark:divide-gray-700">
                        <div class="flex items-center justify-between px-6 py-4">
                            <h3 class="font-bold text-gray-900 dark:text-white">Mietdetails</h3>
                            <button type="button" wire:click="$set('currentStep',3)" class="text-xs text-blue-600 dark:text-blue-400 hover:underline">Bearbeiten</button>
                        </div>
                        <dl class="px-6 py-4 grid grid-cols-1 sm:grid-cols-2 gap-x-8 gap-y-2 text-sm">
                            <div><dt class="text-gray-500 inline">Mietbeginn: </dt><dd class="font-medium inline">{{ $startDate ? $startDate->format('d.m.Y') : '–' }}</dd></div>
                            <div><dt class="text-gray-500 inline">Mietende: </dt><dd class="font-medium inline">{{ $endDate ? $endDate->format('d.m.Y') : '–' }}</dd></div>
                            <div><dt class="text-gray-500 inline">Dauer: </dt><dd class="font-medium inline">{{ $duration }} {{ $duration === 1 ? 'Monat' : 'Monate' }}</dd></div>
                            @if(!empty($rd['message']))
                                <div class="sm:col-span-2"><dt class="text-gray-500 inline">Nachricht: </dt><dd class="font-medium inline">{{ $rd['message'] }}</dd></div>
                            @endif
                        </dl>
                    </div>

                    {{-- Zahlungsinformationen --}}
                    <div class="bg-white dark:bg-gray-800 rounded-xl shadow divide-y divide-gray-100 dark:divide-gray-700">
                        <div class="flex items-center justify-between px-6 py-4">
                            <h3 class="font-bold text-gray-900 dark:text-white">Zahlungsinformationen</h3>
                            <button type="button" wire:click="$set('currentStep',4)" class="text-xs text-blue-600 dark:text-blue-400 hover:underline">Bearbeiten</button>
                        </div>
                        <dl class="px-6 py-4 grid grid-cols-1 sm:grid-cols-2 gap-x-8 gap-y-2 text-sm">
                            <div><dt class="text-gray-500 inline">Zahlungsart: </dt><dd class="font-medium inline uppercase">{{ $pd['payment_method'] ?? '–' }}</dd></div>
                            @if(!empty($pd['account_holder']))
                                <div><dt class="text-gray-500 inline">Kontoinhaber: </dt><dd class="font-medium inline">{{ $pd['account_holder'] }}</dd></div>
                            @endif
                            @if($iban)
                                <div><dt class="text-gray-500 inline">IBAN: </dt><dd class="font-medium inline font-mono">{{ $maskedIban }}</dd></div>
                            @endif
                            @if(!empty($pd['bic']))
                                <div><dt class="text-gray-500 inline">BIC: </dt><dd class="font-medium inline font-mono">{{ $pd['bic'] }}</dd></div>
                            @endif
                            @if(!empty($pd['bank_name']))
                                <div><dt class="text-gray-500 inline">Bank: </dt><dd class="font-medium inline">{{ $pd['bank_name'] }}</dd></div>
                            @endif
                            <div class="sm:col-span-2"><dt class="text-gray-500 inline">SEPA-Mandat: </dt>
                                <dd class="font-medium inline {{ !empty($pd['sepa_mandate_accepted']) ? 'text-green-600' : 'text-red-500' }}">
                                    {{ !empty($pd['sepa_mandate_accepted']) ? '✓ Akzeptiert' : '✗ Nicht akzeptiert' }}
                                </dd>
                            </div>
                        </dl>
                        <div class="px-6 py-4 text-sm">
                            <div class="font-semibold text-gray-700 dark:text-gray-300 mb-2">Rechnungsanschrift</div>
                            @if(!$billDiff)
                                <span class="text-gray-500 italic">Identisch mit Postadresse</span>
                            @else
                                <dl class="grid grid-cols-1 sm:grid-cols-2 gap-x-8 gap-y-1">
                                    <div><dt class="text-gray-500 inline">Straße: </dt><dd class="font-medium inline">{{ $pd['billing_street'] ?? '–' }}</dd></div>
                                    @if(!empty($pd['billing_address2']))
                                        <div><dt class="text-gray-500 inline">Zusatz: </dt><dd class="font-medium inline">{{ $pd['billing_address2'] }}</dd></div>
                                    @endif
                                    <div><dt class="text-gray-500 inline">PLZ / Stadt: </dt><dd class="font-medium inline">{{ ($pd['billing_zip'] ?? '') }} {{ ($pd['billing_city'] ?? '') }}</dd></div>
                                    <div><dt class="text-gray-500 inline">Land: </dt><dd class="font-medium inline">{{ $pd['billing_country'] ?? 'Deutschland' }}</dd></div>
                                </dl>
                            @endif
                        </div>
                    </div>

                    {{-- Anhänge --}}
                    @if(!empty($ad['attachments']))
                        <div class="bg-white dark:bg-gray-800 rounded-xl shadow divide-y divide-gray-100 dark:divide-gray-700">
                            <div class="flex items-center justify-between px-6 py-4">
                                <h3 class="font-bold text-gray-900 dark:text-white">Anhänge ({{ count($ad['attachments']) }})</h3>
                                <button type="button" wire:click="$set('currentStep',5)" class="text-xs text-blue-600 dark:text-blue-400 hover:underline">Bearbeiten</button>
                            </div>
                            <ul class="px-6 py-4 space-y-1 text-sm">
                                @foreach($ad['attachments'] as $file)
                                    <li class="flex items-center gap-2 text-gray-700 dark:text-gray-300">
                                        <svg class="w-4 h-4 text-gray-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/>
                                        </svg>
                                        {{ basename($file) }}
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    {{-- Hinweis --}}
                    <div class="bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800 rounded-lg p-4 text-sm text-amber-800 dark:text-amber-200">
                        <strong>Bitte prüfen Sie Ihre Angaben sorgfältig.</strong>
                        Nach dem Absenden erhalten Sie eine Bestätigung per E-Mail. Wir melden uns in Kürze bei Ihnen.
                    </div>

                    {{-- Navigation --}}
                    <div class="flex justify-between items-center pt-2">
                        <button type="button" wire:click="previousStep"
                                class="inline-flex items-center gap-2 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-200 font-semibold py-3 px-6 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors duration-200">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                            </svg>
                            Zurück
                        </button>
                        <button wire:click="submit" wire:loading.attr="disabled"
                                class="inline-flex items-center gap-2 bg-green-600 hover:bg-green-700 disabled:opacity-50 disabled:cursor-not-allowed text-white font-semibold py-3 px-8 rounded-lg transition-colors duration-200">
                            <span wire:loading.remove wire:target="submit" class="inline-flex items-center gap-2">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                Anfrage absenden
                            </span>
                            <span wire:loading wire:target="submit">Wird gesendet…</span>
                        </button>
                    </div>
                </div>
            @endif

        </div>
    @else
        <div class="text-center py-12">
            <div class="text-gray-500 dark:text-gray-400 text-lg">Kein Board verfügbar</div>
        </div>
    @endif
</x-filament-panels::page>
