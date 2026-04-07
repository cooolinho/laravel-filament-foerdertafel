<div class="space-y-6">
    {{-- Customer Information --}}
    <div class="bg-white dark:bg-gray-800 rounded-lg p-4 border border-gray-200 dark:border-gray-700">
        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
            <svg class="w-5 h-5 text-primary-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
            </svg>
            Kunde
        </h3>
        <dl class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Name</dt>
                <dd class="mt-1 text-base text-gray-900 dark:text-white">{{ $rental->customer->{\App\Models\Customer::name} ?? 'N/A' }}</dd>
            </div>
            <div>
                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">E-Mail</dt>
                <dd class="mt-1 text-base text-gray-900 dark:text-white">
                    <a href="mailto:{{ $rental->customer->{\App\Models\Customer::email} }}" class="text-primary-600 hover:text-primary-500">
                        {{ $rental->customer->{\App\Models\Customer::email} ?? 'N/A' }}
                    </a>
                </dd>
            </div>
            @if($rental->customer->{\App\Models\Customer::phone})
                <div>
                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Telefon</dt>
                    <dd class="mt-1 text-base text-gray-900 dark:text-white">
                        <a href="tel:{{ $rental->customer->{\App\Models\Customer::phone} }}" class="text-primary-600 hover:text-primary-500">
                            {{ $rental->customer->{\App\Models\Customer::phone} }}
                        </a>
                    </dd>
                </div>
            @endif
        </dl>
    </div>

    {{-- Rental Period --}}
    <div class="bg-white dark:bg-gray-800 rounded-lg p-4 border border-gray-200 dark:border-gray-700">
        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
            <svg class="w-5 h-5 text-primary-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
            </svg>
            Mietdauer
        </h3>
        <dl class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Startdatum</dt>
                <dd class="mt-1 text-base text-gray-900 dark:text-white">{{ $rental->{\App\Models\Rental::start_date}->format('d.m.Y') }}</dd>
            </div>
            <div>
                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Enddatum</dt>
                <dd class="mt-1 text-base text-gray-900 dark:text-white">{{ $rental->{\App\Models\Rental::end_date}->format('d.m.Y') }}</dd>
            </div>
            <div>
                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Dauer</dt>
                <dd class="mt-1 text-base text-gray-900 dark:text-white">
                    @php
                        $months = $rental->rental_months;
                    @endphp
                    {{ $months }} {{ $months === 1 ? 'Monat' : 'Monate' }}
                </dd>
            </div>
        </dl>
    </div>

    {{-- Rented Fields --}}
    <div class="bg-white dark:bg-gray-800 rounded-lg p-4 border border-gray-200 dark:border-gray-700">
        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
            <svg class="w-5 h-5 text-primary-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
            </svg>
            Gemietete Felder
            <span class="ml-auto text-sm font-normal text-gray-500 dark:text-gray-400">
                {{ $rental->fields->count() }} {{ $rental->fields->count() === 1 ? 'Feld' : 'Felder' }}
            </span>
        </h3>

        <div class="space-y-2">
            @foreach($rental->fields->sortBy(\App\Models\Field::row) as $field)
                <div class="flex items-center justify-between p-3 rounded-lg bg-gray-50 dark:bg-gray-700/50">
                    <div class="flex items-center gap-3">
                        <div class="flex-shrink-0 w-10 h-10 rounded-lg bg-primary-500 text-white flex items-center justify-center font-semibold text-xs">
                            {{ $field->getIdentifier() }}
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-900 dark:text-white">
                                {{ $field->{\App\Models\Field::name} }}
                            </p>
                            <p class="text-xs text-gray-500 dark:text-gray-400">
                                Position: Zeile {{ $field->{\App\Models\Field::row} }}, Spalte {{ $field->{\App\Models\Field::column} }}
                                @if($field->{\App\Models\Field::width} > 1 || $field->{\App\Models\Field::height} > 1)
                                    ({{ $field->{\App\Models\Field::width} }}x{{ $field->{\App\Models\Field::height} }})
                                @endif
                            </p>
                        </div>
                    </div>
                    <div class="text-right">
                        <p class="text-sm font-semibold text-gray-900 dark:text-white">
                            {{ number_format($field->{\App\Models\Field::price_per_month}, 2, ',', '.') }} €
                        </p>
                        <p class="text-xs text-gray-500 dark:text-gray-400">pro Monat</p>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    {{-- Price Summary --}}
    <div class="bg-primary-50 dark:bg-primary-500/10 rounded-lg p-4 border border-primary-200 dark:border-primary-500/20">
        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Preisübersicht</h3>
        <dl class="space-y-2">
            <div class="flex justify-between text-sm">
                <dt class="text-gray-600 dark:text-gray-400">Preis pro Monat</dt>
                <dd class="font-medium text-gray-900 dark:text-white">
                    {{ number_format($rental->fields->sum(\App\Models\Field::price_per_month), 2, ',', '.') }} €
                </dd>
            </div>
            <div class="flex justify-between text-sm">
                <dt class="text-gray-600 dark:text-gray-400">Anzahl Monate</dt>
                <dd class="font-medium text-gray-900 dark:text-white">
                    {{ $rental->rental_months }}
                </dd>
            </div>
            <div class="border-t border-primary-200 dark:border-primary-500/20 pt-2 mt-2">
                <div class="flex justify-between">
                    <dt class="text-base font-semibold text-gray-900 dark:text-white">Gesamtpreis</dt>
                    <dd class="text-base font-bold text-primary-600 dark:text-primary-400">
                        {{ number_format($rental->{\App\Models\Rental::total_price}, 2, ',', '.') }} €
                    </dd>
                </div>
            </div>
        </dl>
    </div>

    {{-- Status Badge --}}
    <div class="flex justify-center">
        @php
            $statusColors = [
                \App\Models\Rental::STATUS_ACTIVE => 'bg-green-100 text-green-800 dark:bg-green-800 dark:text-green-100',
                \App\Models\Rental::STATUS_COMPLETED => 'bg-blue-100 text-blue-800 dark:bg-blue-800 dark:text-blue-100',
                \App\Models\Rental::STATUS_CANCELLED => 'bg-red-100 text-red-800 dark:bg-red-800 dark:text-red-100',
            ];
            $statusColor = $statusColors[$rental->{\App\Models\Rental::status}] ?? 'bg-gray-100 text-gray-800';
        @endphp
        <span class="px-4 py-2 rounded-full text-sm font-semibold {{ $statusColor }}">
            Status: {{ $rental->getStatusLabel() }}
        </span>
    </div>
</div>
