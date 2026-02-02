<x-filament-panels::page>
    @if($this->board)
        <div class="space-y-6">
            {{-- Board Header --}}
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <h2 class="text-2xl font-bold text-gray-900 dark:text-white">
                            {{ $this->board->{\App\Models\Board::name} }}
                        </h2>
                        @if($this->board->{\App\Models\Board::description})
                            <p class="mt-2 text-gray-600 dark:text-gray-400">
                                {{ $this->board->{\App\Models\Board::description} }}
                            </p>
                        @endif
                    </div>
                    <div class="text-right">
                        <div class="text-sm text-gray-500 dark:text-gray-400">
                            {{ $this->board->location->{\App\Models\Location::name} }}
                        </div>
                        <div class="text-xs text-gray-400 dark:text-gray-500">
                            {{ $this->board->{\App\Models\Board::rows} }} × {{ $this->board->{\App\Models\Board::columns} }} Felder
                        </div>
                    </div>
                </div>
            </div>

            {{-- Legend --}}
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4">
                <div class="flex flex-wrap gap-6 items-center">
                    <h3 class="text-sm font-semibold text-gray-900 dark:text-white">Legende:</h3>
                    <div class="flex items-center gap-2">
                        <div class="w-6 h-6 rounded border-2 border-green-500 bg-green-500/20"></div>
                        <span class="text-sm text-gray-600 dark:text-gray-400">Verfügbar</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <div class="w-6 h-6 rounded border-2 border-yellow-500 bg-yellow-500/20"></div>
                        <span class="text-sm text-gray-600 dark:text-gray-400">Vermietet</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <div class="w-6 h-6 rounded border-2 border-blue-500 bg-blue-500/20"></div>
                        <span class="text-sm text-gray-600 dark:text-gray-400">Reserviert</span>
                    </div>
                </div>
            </div>

            {{-- Board Grid with Background --}}
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow overflow-hidden">
                @php
                    $defaultBackground = 'img/board-preview.jpg';
                    $backgroundImage = $this->board->{\App\Models\Board::background_image};
                    $backgroundUrl = $backgroundImage ? \Storage::url($backgroundImage) : asset($defaultBackground);
                    $offsetX = $this->board->{\App\Models\Board::grid_offset_x} ?? 0;
                    $offsetY = $this->board->{\App\Models\Board::grid_offset_y} ?? 0;
                    $gap = $this->board->{\App\Models\Board::grid_gap} ?? 12;
                    $hasBackground = !empty($backgroundImage);
                @endphp

                <div class="relative w-full overflow-auto"
                    style="background-image: url('{{ $backgroundUrl }}');
                           background-size: 100% 100%;
                           background-position: center;
                           background-repeat: no-repeat;
                           height: 1600px;">

                    {{-- Grid Container with Padding (Offset) --}}
                    @php
                        $columns = $this->board->{\App\Models\Board::columns};
                        $rows = $this->board->{\App\Models\Board::rows};

                        // Berechne totale Gap-Breite/Höhe
                        // Zwischen N Spalten gibt es (N-1) Gaps
                        $totalGapWidth = ($columns - 1) * $gap;
                        $totalGapHeight = ($rows - 1) * $gap;

                        $doubleOffsetX = $offsetX * 2;
                        $doubleOffsetY = $offsetY * 2;

                        // Berechne verfügbare Breite/Höhe nach Abzug von Offset und Gap
                        $availableWidth = "calc(100% - {$doubleOffsetX}px - {$totalGapWidth}px)";
                        $availableHeight = "calc(100% - {$doubleOffsetY}px - {$totalGapHeight}px)";

                        // Jede Spalte/Reihe bekommt einen gleichen Anteil der verfügbaren Fläche
                        $columnWidth = "calc({$availableWidth} / {$columns})";
                        $rowHeight = "calc({$availableHeight} / {$rows})";
                    @endphp

                    <div class="w-full h-full"
                         style="position: absolute;
                                top: {{ $offsetY }}px;
                                left: {{ $offsetX }}px;
                                right: {{ $offsetX }}px;
                                bottom: {{ $offsetY }}px;">

                        <div class="grid w-full h-full"
                             style="grid-template-columns: repeat({{ $columns }}, {{ $columnWidth }});
                                    grid-template-rows: repeat({{ $rows }}, {{ $rowHeight }});
                                    gap: {{ $gap }}px;">

                            @foreach($this->getFieldsGrid() as $row => $columns)
                                @foreach($columns as $col => $field)
                                    @if($field === 'occupied')
                                        {{-- Occupied position - invisible placeholder --}}
                                        <div style="grid-column: {{ $col }}; grid-row: {{ $row }};" class="pointer-events-none"></div>

                                    @elseif($field)
                                        @php
                                            $activeRental = $this->getActiveRental($field);
                                            $fieldStatus = $field->{\App\Models\Field::status};
                                            $fieldWidth = $field->{\App\Models\Field::width};
                                            $fieldHeight = $field->{\App\Models\Field::height};

                                            // Style based on status
                                            $borderColor = "border-white/20 bg-black/5 backdrop-blur-sm";
                                        @endphp

                                        <div class="relative group {{ $fieldStatus === \App\Models\Field::STATUS_AVAILABLE ? 'cursor-pointer' : '' }}"
                                             style="grid-column: {{ $col }} / span {{ $fieldWidth }};
                                                    grid-row: {{ $row }} / span {{ $fieldHeight }};">

                                            <div class="flex flex-col h-full p-px border-2 {{ $borderColor }} backdrop-blur-sm transition-all duration-300"
                                                 style="min-height: 10px;">

                                                @if($activeRental)
                                                    {{-- Rented Field - Show Customer --}}
                                                    <div class="bg-black/70 text-white px-4 py-3 rounded-lg w-full h-full text-center">
                                                        <div class="font-bold">
                                                            {{ $activeRental->customer->{\App\Models\Customer::name} ?? 'N/A' }}
                                                        </div>
                                                    </div>

                                                @elseif($fieldStatus === \App\Models\Field::STATUS_AVAILABLE)
                                                    {{-- Available Field --}}
                                                    <div class="rounded-lg w-full h-full">
                                                        <div class="flex items-center justify-center gap-2 mb-2 h-full">
                                                            <div class="w-6 h-6 rounded border-2 border-green-500 bg-green-500/20"></div>
                                                        </div>
                                                    </div>

                                                    {{-- Hover Effect for Available Fields --}}
                                                    <div class="absolute inset-0 bg-green-500/0 group-hover:bg-green-500/10 transition-all duration-300 rounded-lg pointer-events-none"></div>

                                                @elseif($fieldStatus === \App\Models\Field::STATUS_RESERVED)
                                                    {{-- Reserved Field --}}
                                                    <div class="rounded-lg w-full h-full">
                                                        <div class="flex items-center justify-center gap-2 mb-2 h-full">
                                                            <div class="w-6 h-6 rounded border-2 border-blue-500 bg-blue-500/20"></div>
                                                        </div>
                                                    </div>
                                                @elseif($fieldStatus === \App\Models\Field::STATUS_RENTED)
                                                    {{-- Reserved Field --}}
                                                    <div class="rounded-lg w-full h-full">
                                                        <div class="flex items-center justify-center gap-2 mb-2 h-full">
                                                            <div class="w-6 h-6 rounded border-2 border-yellow-500 bg-yellow-500/20"></div>
                                                        </div>
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                    @endif
                                @endforeach
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>

            {{-- Statistics --}}
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                @php
                    $totalFields = $this->board->fields()->count();
                    $availableFields = $this->board->fields()->where(\App\Models\Field::status, \App\Models\Field::STATUS_AVAILABLE)->count();
                    $rentedFields = $this->board->fields()->where(\App\Models\Field::status, \App\Models\Field::STATUS_RENTED)->count();
                @endphp

                <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6 text-center">
                    <div class="text-3xl font-bold text-gray-900 dark:text-white">{{ $totalFields }}</div>
                    <div class="text-sm text-gray-600 dark:text-gray-400 mt-1">Gesamt Felder</div>
                </div>
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6 text-center">
                    <div class="text-3xl font-bold text-green-600">{{ $availableFields }}</div>
                    <div class="text-sm text-gray-600 dark:text-gray-400 mt-1">Verfügbar</div>
                </div>
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6 text-center">
                    <div class="text-3xl font-bold text-yellow-600">{{ $rentedFields }}</div>
                    <div class="text-sm text-gray-600 dark:text-gray-400 mt-1">Vermietet</div>
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
