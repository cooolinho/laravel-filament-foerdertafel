<x-filament-widgets::widget>
    <x-filament::section>
        <x-slot name="heading">
            <div class="flex items-center justify-between w-full">
                <span>Board Felder</span>
                <div class="flex items-center gap-3">
                    @if($this->record)
                        <x-filament::badge color="gray">
                            {{ $this->record->{\App\Models\Board::rows} }} x {{ $this->record->{\App\Models\Board::columns} }}
                        </x-filament::badge>

                        {{ ($this->fillAllFieldsAction)(['record' => $this->record]) }}
                    @endif
                </div>
            </div>
        </x-slot>

        @if($this->record && count($this->getFieldsGrid()) > 0)
            <div class="space-y-4">
                {{-- Legend --}}
                <div class="flex flex-wrap gap-4 p-4 bg-gray-50 dark:bg-gray-800 rounded-lg">
                    <div class="flex items-center gap-2">
                        <div class="w-4 h-4 rounded border-2 border-green-500 bg-green-50"></div>
                        <span class="text-sm text-gray-600 dark:text-gray-400">Verfügbar</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <div class="w-4 h-4 rounded border-2 border-yellow-500 bg-yellow-50"></div>
                        <span class="text-sm text-gray-600 dark:text-gray-400">Vermietet</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <div class="w-4 h-4 rounded border-2 border-blue-500 bg-blue-50"></div>
                        <span class="text-sm text-gray-600 dark:text-gray-400">Reserviert</span>
                    </div>
                </div>

                {{-- Fields Grid --}}
                <div class="overflow-x-auto">
                    <div class="inline-block min-w-full align-middle">
                        @php
                            $gap = $this->record->{\App\Models\Board::grid_gap} ?? 12;
                        @endphp
                        <div class="grid p-4"
                             style="grid-template-columns: repeat({{ $this->record->{\App\Models\Board::columns} }}, minmax(120px, 1fr));
                                    grid-template-rows: repeat({{ $this->record->{\App\Models\Board::rows} }}, minmax(120px, auto));
                                    gap: {{ $gap }}px;">
                            @foreach($this->getFieldsGrid() as $row => $columns)
                                @foreach($columns as $col => $field)
                                    @if($field === 'occupied')
                                        {{-- Occupied position - render invisible placeholder --}}
                                        <div style="grid-column: {{ $col }}; grid-row: {{ $row }};" class="pointer-events-none"></div>
                                    @elseif($field)
                                        @php
                                            $activeRental = $this->getActiveRental($field);
                                            $fieldStatus = $field->{\App\Models\Field::status};
                                            $borderColor = match($fieldStatus) {
                                                \App\Models\Field::STATUS_AVAILABLE => 'border-green-500 bg-green-50 dark:bg-green-950',
                                                \App\Models\Field::STATUS_RENTED => 'border-yellow-500 bg-yellow-50 dark:bg-yellow-950',
                                                \App\Models\Field::STATUS_RESERVED => 'border-blue-500 bg-blue-50 dark:bg-blue-950',
                                                default => 'border-gray-500 bg-gray-50 dark:bg-gray-800'
                                            };
                                            $fieldWidth = $field->{\App\Models\Field::width};
                                            $fieldHeight = $field->{\App\Models\Field::height};
                                            $fieldRow = $field->{\App\Models\Field::row};
                                            $fieldColumn = $field->{\App\Models\Field::column};
                                            $fieldIdentifier = chr(64 + $fieldRow) . $fieldColumn;
                                        @endphp

                                        <div class="relative group" style="grid-column: {{ $col }} / span {{ $fieldWidth }}; grid-row: {{ $row }} / span {{ $fieldHeight }};">
                                            <a href="{{ \App\Filament\Admin\Resources\Fields\FieldResource::getUrl('view', ['record' => $field->id]) }}"
                                               class="flex flex-col p-4 rounded-lg border-2 transition-all hover:shadow-xl hover:scale-105 h-full {{ $borderColor }}"
                                               style="min-height: 120px;">

                                                {{-- Position & Name --}}
                                                <div class="flex items-start justify-between mb-2">
                                                    <div class="text-xs font-mono text-gray-500 dark:text-gray-400">
{{--                                                        [{{ $field->{\App\Models\Field::row} }},{{ $field->{\App\Models\Field::column} }}]--}}
                                                        [{{ $fieldIdentifier }}]
                                                    </div>
                                                    @if($fieldWidth > 1 || $fieldHeight > 1)
                                                        <x-filament::badge color="gray" size="xs">
                                                            {{ $fieldWidth }}x{{ $fieldHeight }}
                                                        </x-filament::badge>
                                                    @endif
                                                </div>

                                                {{-- Field Name --}}
                                                <div class="font-bold text-sm text-gray-900 dark:text-white mb-2 line-clamp-2">
                                                    {{ $field->{\App\Models\Field::name} }}
                                                </div>

                                                {{-- Status Badge --}}
                                                <div class="mt-auto">
                                                    <x-filament::badge :color="$this->getFieldStatusColor($fieldStatus)" size="sm">
                                                        {{ $this->getFieldStatusLabel($fieldStatus) }}
                                                    </x-filament::badge>
                                                </div>

                                                {{-- Rental Info --}}
                                                @if($activeRental)
                                                    <div class="mt-2 pt-2 border-t border-gray-200 dark:border-gray-700">
                                                        <div class="text-xs text-gray-600 dark:text-gray-400 truncate">
                                                            <span class="font-semibold">Mieter:</span> {{ $activeRental->customer->{\App\Models\Customer::name} ?? 'N/A' }}
                                                        </div>
                                                        <div class="text-xs text-gray-500 dark:text-gray-500 mt-1">
                                                            bis {{ $activeRental->{\App\Models\Rental::end_date}?->format('d.m.Y') }}
                                                        </div>
                                                    </div>
                                                @else
                                                    <div class="mt-2 pt-2 border-t border-gray-200 dark:border-gray-700">
                                                        <div class="text-xs font-semibold text-gray-700 dark:text-gray-300">
                                                            {{ number_format($field->{\App\Models\Field::price_per_month}, 2) }} €/Monat
                                                        </div>
                                                    </div>
                                                @endif
                                            </a>

                                            {{-- Quick Actions (on hover) --}}
                                            <div class="absolute top-2 right-2 opacity-0 group-hover:opacity-100 transition-opacity flex gap-1 z-10">
                                                <a href="{{ \App\Filament\Admin\Resources\Fields\FieldResource::getUrl('edit', ['record' => $field->id]) }}"
                                                   class="p-1.5 bg-white dark:bg-gray-800 rounded-lg shadow-lg hover:bg-gray-100 dark:hover:bg-gray-700"
                                                   title="Bearbeiten">
                                                    <x-filament::icon icon="heroicon-o-pencil" class="w-4 h-4 text-gray-600 dark:text-gray-400" />
                                                </a>
                                                @if($fieldStatus === \App\Models\Field::STATUS_AVAILABLE)
                                                    <a href="{{ \App\Filament\Admin\Resources\Rentals\RentalResource::getUrl('create', ['field_id' => $field->id]) }}"
                                                        class="p-1.5 bg-white dark:bg-gray-800 rounded-lg shadow-lg hover:bg-gray-100 dark:hover:bg-gray-700"
                                                        title="Vermieten">
                                                        <x-filament::icon icon="heroicon-o-plus-circle" class="w-4 h-4 text-green-600 dark:text-green-400" />
                                                    </a>
                                                @endif
                                            </div>
                                        </div>
                                    @else
                                        <button
                                            wire:click="openCreateFieldModal({{ $row }}, {{ $col }})"
                                            class="rounded-lg border-2 border-dashed border-gray-300 dark:border-gray-700 bg-gray-100 dark:bg-gray-900 hover:bg-gray-200 dark:hover:bg-gray-800 hover:border-gray-400 dark:hover:border-gray-600 transition-all group cursor-pointer"
                                            style="grid-column: {{ $col }}; grid-row: {{ $row }}; min-height: 120px;"
                                            title="Neues Feld erstellen">
                                            <div class="flex flex-col items-center justify-center h-full opacity-50 group-hover:opacity-100 transition-opacity">
                                                <x-filament::icon
                                                    icon="heroicon-o-plus-circle"
                                                    class="w-8 h-8 text-gray-400 dark:text-gray-600 group-hover:text-gray-600 dark:group-hover:text-gray-400" />
                                                <span class="text-xs text-gray-500 dark:text-gray-400 mt-2">
                                                    [{{ $row }},{{ $col }}]
                                                </span>
                                                <span class="text-xs font-semibold text-gray-600 dark:text-gray-300 mt-1">
                                                    Feld erstellen
                                                </span>
                                            </div>
                                        </button>
                                    @endif
                                @endforeach
                            @endforeach
                        </div>
                    </div>
                </div>

                {{-- Summary Statistics --}}
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-6 p-4 bg-gray-50 dark:bg-gray-800 rounded-lg">
                    @php
                        $totalFields = $this->record->fields()->count();
                        $availableFields = $this->record->fields()->where(\App\Models\Field::status, \App\Models\Field::STATUS_AVAILABLE)->count();
                        $rentedFields = $this->record->fields()->where(\App\Models\Field::status, \App\Models\Field::STATUS_RENTED)->count();
                    @endphp

                    <div class="text-center">
                        <div class="text-2xl font-bold text-gray-900 dark:text-white">{{ $totalFields }}</div>
                        <div class="text-sm text-gray-600 dark:text-gray-400">Gesamt Felder</div>
                    </div>
                    <div class="text-center">
                        <div class="text-2xl font-bold text-green-600 dark:text-green-400">{{ $availableFields }}</div>
                        <div class="text-sm text-gray-600 dark:text-gray-400">Verfügbar</div>
                    </div>
                    <div class="text-center">
                        <div class="text-2xl font-bold text-yellow-600 dark:text-yellow-400">{{ $rentedFields }}</div>
                        <div class="text-sm text-gray-600 dark:text-gray-400">Vermietet</div>
                    </div>
                </div>
            </div>
        @else
            <div class="text-center py-12">
                <x-filament::icon icon="heroicon-o-squares-2x2" class="w-16 h-16 mx-auto text-gray-400 dark:text-gray-600 mb-4" />
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">
                    Keine Felder vorhanden
                </h3>
                <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">
                    Dieses Board hat noch keine Felder. Erstellen Sie welche, um loszulegen.
                </p>
                @if($this->record)
                    <x-filament::button
                        href="{{ \App\Filament\Admin\Resources\Fields\FieldResource::getUrl('create', ['board_id' => $this->record->id]) }}"
                        color="primary">
                        Erstes Feld erstellen
                    </x-filament::button>
                @endif
            </div>
        @endif
    </x-filament::section>

    <x-filament-actions::modals />
</x-filament-widgets::widget>
