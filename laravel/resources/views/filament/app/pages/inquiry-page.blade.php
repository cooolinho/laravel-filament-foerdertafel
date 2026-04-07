<x-filament-panels::page>
    @if($this->board)
        <div class="space-y-6">
            {{-- Board Header --}}
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <h2 class="text-2xl font-bold text-gray-900 dark:text-white">
                            Anfrage für {{ $this->board->{\App\Models\Board::name} }}
                        </h2>
                        @if($this->board->{\App\Models\Board::description})
                            <p class="mt-2 text-gray-600 dark:text-gray-400">
                                {{ $this->board->{\App\Models\Board::description} }}
                            </p>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Instructions --}}
            <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-4">
                <div class="flex items-start gap-3">
                    <svg class="w-6 h-6 text-blue-600 dark:text-blue-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <div>
                        <h3 class="font-semibold text-blue-900 dark:text-blue-100">So funktioniert's:</h3>
                        <ol class="mt-2 space-y-1 text-sm text-blue-800 dark:text-blue-200 list-decimal list-inside">
                            <li>Wählen Sie die gewünschten Felder im Raster aus (klicken Sie auf die verfügbaren Felder, max. <b>{{ \App\Models\Setting::getMaxFieldsPerCustomer() }} Felder</b>)</li>
                            <li>Die Auswahl muss immer ein zusammenhängendes Rechteck ergeben (max. {{ \App\Models\Setting::getMaxSelectionRows() }} Zeile(n) × {{ \App\Models\Setting::getMaxSelectionCols() }} Spalte(n))</li>
                            <li>Füllen Sie das Formular mit Ihren Kontaktdaten aus</li>
                            <li>Geben Sie den gewünschten Zeitraum an</li>
                            <li>Senden Sie die Anfrage ab - wir melden uns bei Ihnen!</li>
                        </ol>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                {{-- Left Column: Board Grid --}}
                <div class="lg:col-span-2 space-y-4">
                    {{-- Legend --}}
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
                                <span class="text-sm text-gray-600 dark:text-gray-400">Vermietet (mit Kundenname)</span>
                            </div>
                        </div>
                    </div>

                    {{-- Board Grid --}}
                    <div class="bg-white dark:bg-gray-800 rounded-lg shadow overflow-hidden">
                        @php
                            $defaultBackground = 'img/board-preview.jpg';
                            $backgroundImage = $this->board->{\App\Models\Board::background_image};
                            $backgroundUrl = $backgroundImage ? \Storage::url($backgroundImage) : asset($defaultBackground);
                            $offsetX = $this->board->{\App\Models\Board::grid_offset_x} ?? 0;
                            $offsetY = $this->board->{\App\Models\Board::grid_offset_y} ?? 0;
                            $gap = $this->board->{\App\Models\Board::grid_gap} ?? 12;
                        @endphp

                        <div class="relative w-full overflow-auto"
                            style="background-image: url('{{ $backgroundUrl }}');
                                   background-size: 100% 100%;
                                   background-position: center;
                                   background-repeat: no-repeat;
                                   height: 1400px;">

                            @php
                                $columns = $this->board->{\App\Models\Board::columns};
                                $rows = $this->board->{\App\Models\Board::rows};
                                $totalGapWidth = ($columns - 1) * $gap;
                                $totalGapHeight = ($rows - 1) * $gap;
                                $doubleOffsetX = $offsetX * 2;
                                $doubleOffsetY = $offsetY * 2;
                                $availableWidth = "calc(100% - {$doubleOffsetX}px - {$totalGapWidth}px)";
                                $availableHeight = "calc(100% - {$doubleOffsetY}px - {$totalGapHeight}px)";
                                $columnWidth = "calc({$availableWidth} / {$columns})";
                                $rowHeight = "calc({$availableHeight} / {$rows})";
                            @endphp

                            <div class="w-full h-full"
                                 style="position: absolute;
                                        top: {{ $offsetY }}px;
                                        left: {{ $offsetX }}px;
                                        right: {{ $offsetX }}px;
                                        bottom: {{ $offsetY }}px;">

                                @php
                                    // Calculate cell dimensions
                                    $totalWidth = 100; // percentage
                                    $totalHeight = 100; // percentage
                                    $cellWidth = ($totalWidth - (($columns - 1) * $gap / 16)) / $columns; // Adjust for gap
                                    $cellHeight = ($totalHeight - (($rows - 1) * $gap / 16)) / $rows; // Adjust for gap in percentage
                                @endphp

                                {{-- Render all fields with absolute positioning --}}
                                @foreach($this->getFields() as $field)
                                    @php
                                        $fieldId = $field->id;
                                        $fieldRow = $field->{\App\Models\Field::row};
                                        $fieldCol = $field->{\App\Models\Field::column};
                                        $fieldWidth = $field->{\App\Models\Field::width};
                                        $fieldHeight = $field->{\App\Models\Field::height};
                                        $fieldStatus = $field->{\App\Models\Field::status};
                                        $fieldIdentifier = $field->getIdentifier();
                                        $pricePerMonth = $field->{\App\Models\Field::price_per_month};
                                        $isSelected = in_array($fieldId, $this->selectedFields);
                                        $activeRental = $this->getActiveRental($field);
                                        $isAvailable = $fieldStatus === \App\Models\Field::STATUS_AVAILABLE && !$activeRental;

                                        // Calculate position (1-based to 0-based)
                                        $colIndex = $fieldCol - 1;
                                        $rowIndex = $fieldRow - 1;

                                        // Calculate left and top position including gaps
                                        $leftPercent = ($colIndex * $cellWidth) + ($colIndex * ($gap / 16));
                                        $topPercent = ($rowIndex * $cellHeight) + ($rowIndex * ($gap / 16));
                                        $widthPercent = ($fieldWidth * $cellWidth) + (($fieldWidth - 1) * ($gap / 16));
                                        $heightPercent = ($fieldHeight * $cellHeight) + (($fieldHeight - 1) * ($gap / 16));
                                    @endphp

                                    <div class="absolute {{ $isAvailable ? 'cursor-pointer' : '' }}"
                                         style="left: {{ $leftPercent }}%;
                                                top: {{ $topPercent }}%;
                                                width: {{ $widthPercent }}%;
                                                height: {{ $heightPercent }}%;"
                                         @if($isAvailable) wire:click="toggleField({{ $fieldId }})" @endif>

                                        @if($activeRental)
                                            {{-- Rented Field - Show Customer --}}
                                            <div class="flex flex-col h-full p-2 border-2 border-yellow-500 bg-yellow-500/20 backdrop-blur-sm rounded-lg"
                                                 style="min-height: 10px;">
                                                <div class="flex flex-col items-center justify-center h-full">
                                                    <div class="text-xs font-bold text-white mt-1 bg-black/70 px-2 py-1 rounded">
                                                        {{ $activeRental->customer->{\App\Models\Customer::name} ?? 'N/A' }}
                                                    </div>
                                                </div>
                                            </div>

                                        @elseif($isAvailable && $isSelected)
                                            {{-- Selected Available Field --}}
                                            <div class="flex flex-col h-full p-2 border-2 border-blue-600 bg-blue-600/80 backdrop-blur-sm transition-all duration-300 rounded-lg"
                                                 style="min-height: 10px;">
                                                <div class="flex flex-col items-center justify-center h-full text-white">
                                                    <svg class="w-8 h-8 mb-1" fill="currentColor" viewBox="0 0 20 20">
                                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                                    </svg>
                                                    <div class="text-xs font-semibold">{{ $fieldIdentifier }}</div>
{{--                                                    <div class="text-xs mt-1">{{ number_format($pricePerMonth, 2, ',', '.') }} €/Monat</div>--}}
                                                </div>
                                            </div>

                                        @elseif($isAvailable)
                                            {{-- Available Field (Not Selected) --}}
                                            <div class="flex flex-col h-full p-2 border-2 border-green-500 bg-green-500/20 backdrop-blur-sm hover:bg-green-500/40 transition-all duration-300 rounded-lg"
                                                 style="min-height: 10px;">
                                                <div class="flex flex-col items-center justify-center h-full">
                                                    <div class="w-6 h-6 rounded border-2 border-green-500 bg-green-500/20 mb-1"></div>
                                                    <div class="text-xs font-semibold text-white">{{ $fieldIdentifier }}</div>
{{--                                                    <div class="text-xs text-white mt-1">{{ number_format($pricePerMonth, 2, ',', '.') }} €/Monat</div>--}}
                                                </div>
                                            </div>

                                        @elseif($fieldStatus === \App\Models\Field::STATUS_RESERVED)
                                            {{-- Reserved Field --}}
                                            <div class="flex flex-col h-full p-2 border-2 border-blue-500 bg-blue-500/20 backdrop-blur-sm rounded-lg"
                                                 style="min-height: 10px;">
                                                <div class="flex flex-col items-center justify-center h-full">
                                                    <div class="text-xs font-semibold text-white bg-black/50 py-1 rounded text-center">Reserviert</div>
                                                </div>
                                            </div>

                                        @else
                                            {{-- Other Status (Rented without active rental) --}}
                                            <div class="flex flex-col h-full p-2 border-2 border-gray-500 bg-gray-500/20 backdrop-blur-sm rounded-lg"
                                                 style="min-height: 10px;">
                                                <div class="flex flex-col items-center justify-center h-full"></div>
                                            </div>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Right Column: Form & Price Summary --}}
                <div class="space-y-6">
                    {{-- Maßanzeige --}}
                    @php $dims = $this->getSelectedFieldDimensions(); @endphp
                    @if($dims)
                        <div class="bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg p-4">
                            <div class="flex items-start gap-2">
                                <svg class="w-5 h-5 text-green-600 dark:text-green-400 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"/>
                                </svg>
                                <div class="flex-1">
                                    <h4 class="text-sm font-semibold text-green-900 dark:text-green-100 mb-2">
                                        Maße Ihrer Auswahl ({{ $dims['rows'] }} × {{ $dims['cols'] }} Felder)
                                    </h4>
                                    <div class="grid grid-cols-2 gap-2">
                                        <div class="bg-white dark:bg-green-900/30 rounded p-2 text-center">
                                            <div class="text-xs text-green-700 dark:text-green-300">Breite</div>
                                            <div class="text-lg font-bold text-green-900 dark:text-green-100">
                                                {{ number_format($dims['width_cm'], 1, ',', '.') }} cm
                                            </div>
                                            @if($dims['cols'] > 1)
                                                <div class="text-xs text-green-600 dark:text-green-400 mt-0.5">
                                                    {{ $dims['cols'] }} × {{ number_format(\App\Models\Setting::get(\App\Models\Setting::field_width_cm, 8.9), 1, ',', '.') }}
                                                    + {{ $dims['cols'] - 1 }} × {{ number_format(\App\Models\Setting::get(\App\Models\Setting::field_gap_cm, 1.2), 1, ',', '.') }}
                                                </div>
                                            @endif
                                        </div>
                                        <div class="bg-white dark:bg-green-900/30 rounded p-2 text-center">
                                            <div class="text-xs text-green-700 dark:text-green-300">Höhe</div>
                                            <div class="text-lg font-bold text-green-900 dark:text-green-100">
                                                {{ number_format($dims['height_cm'], 1, ',', '.') }} cm
                                            </div>
                                            @if($dims['rows'] > 1)
                                                <div class="text-xs text-green-600 dark:text-green-400 mt-0.5">
                                                    {{ $dims['rows'] }} × {{ number_format(\App\Models\Setting::get(\App\Models\Setting::field_height_cm, 5.1), 1, ',', '.') }}
                                                    + {{ $dims['rows'] - 1 }} × {{ number_format(\App\Models\Setting::get(\App\Models\Setting::field_gap_cm, 1.2), 1, ',', '.') }}
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif

                    {{-- Price Summary --}}
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

                            @if(isset($this->data['start_date']) && isset($this->data['end_date']))
                                @php
                                    try {
                                        $startDate = \Carbon\Carbon::parse($this->data['start_date']);
                                        $endDate = \Carbon\Carbon::parse($this->data['end_date']);
                                        $months = $startDate->diffInMonths($endDate);
                                        if ($startDate->copy()->addMonths($months) < $endDate) {
                                            $months++;
                                        }
                                        $months = max(1, $months);
                                    } catch (\Exception $e) {
                                        $months = 1;
                                    }
                                @endphp

                                <div class="flex justify-between items-center pb-3 border-b border-gray-200 dark:border-gray-700">
                                    <span class="text-sm text-gray-600 dark:text-gray-400">Zeitraum:</span>
                                    <span class="font-semibold text-gray-900 dark:text-white">{{ $months }} {{ $months === 1 ? 'Monat' : 'Monate' }}</span>
                                </div>
                            @endif

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

                    {{-- Contact Form --}}
                    <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Ihre Kontaktdaten</h3>

                        <form wire:submit="submit">
                            {{ $this->form }}

                            <div class="mt-6">
                                <button type="submit"
                                        wire:loading.attr="disabled"
                                        class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-3 px-4 rounded-lg transition-colors duration-200 disabled:opacity-50 disabled:cursor-not-allowed">
                                    <div class="flex items-center justify-center gap-2">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                        Anfrage absenden
                                    </div>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    @else
        <div class="text-center py-12">
            <div class="text-gray-500 dark:text-gray-400 text-lg">
                Kein Board verfügbar
            </div>
        </div>
    @endif
</x-filament-panels::page>
