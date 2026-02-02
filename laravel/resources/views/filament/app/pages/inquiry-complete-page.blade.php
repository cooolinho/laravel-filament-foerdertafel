<x-filament-panels::page>
    @if($inquiry)
        <div class="space-y-6">
            {{-- Success Message --}}
            <div class="rounded-lg bg-success-50 dark:bg-success-500/10 p-6 border border-success-200 dark:border-success-500/20">
                <div class="flex items-start gap-4">
                    <div class="flex-shrink-0">
                        <svg class="w-8 h-8 text-success-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <div class="flex-1">
                        <h3 class="text-lg font-semibold text-success-900 dark:text-success-100">
                            Vielen Dank für Ihre Anfrage!
                        </h3>
                        <p class="mt-2 text-sm text-success-700 dark:text-success-200">
                            Ihre Anfrage wurde erfolgreich übermittelt. Wir werden uns in Kürze bei Ihnen melden.
                        </p>
                        <p class="mt-1 text-xs text-success-600 dark:text-success-300">
                            Anfrage-Nr.: #{{ $inquiry->id }}
                        </p>
                    </div>
                </div>
            </div>

            {{-- Inquiry Summary --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                {{-- Contact Information --}}
                <div class="rounded-lg bg-white dark:bg-gray-800 shadow-sm ring-1 ring-gray-950/5 dark:ring-white/10 p-6">
                    <h3 class="text-base font-semibold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
                        <svg class="w-5 h-5 text-primary-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                        </svg>
                        Ihre Kontaktdaten
                    </h3>
                    <dl class="space-y-3">
                        <div>
                            <dt class="text-xs font-medium text-gray-500 dark:text-gray-400">Name</dt>
                            <dd class="mt-1 text-sm text-gray-900 dark:text-white">{{ $inquiry->customer_name }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs font-medium text-gray-500 dark:text-gray-400">E-Mail</dt>
                            <dd class="mt-1 text-sm text-gray-900 dark:text-white">
                                <a href="mailto:{{ $inquiry->customer_email }}" class="text-primary-600 hover:text-primary-500">
                                    {{ $inquiry->customer_email }}
                                </a>
                            </dd>
                        </div>
                        @if($inquiry->customer_phone)
                            <div>
                                <dt class="text-xs font-medium text-gray-500 dark:text-gray-400">Telefon</dt>
                                <dd class="mt-1 text-sm text-gray-900 dark:text-white">
                                    <a href="tel:{{ $inquiry->customer_phone }}" class="text-primary-600 hover:text-primary-500">
                                        {{ $inquiry->customer_phone }}
                                    </a>
                                </dd>
                            </div>
                        @endif
                    </dl>
                </div>

                {{-- Rental Period --}}
                <div class="rounded-lg bg-white dark:bg-gray-800 shadow-sm ring-1 ring-gray-950/5 dark:ring-white/10 p-6">
                    <h3 class="text-base font-semibold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
                        <svg class="w-5 h-5 text-primary-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                        </svg>
                        Mietdauer
                    </h3>
                    <dl class="space-y-3">
                        <div>
                            <dt class="text-xs font-medium text-gray-500 dark:text-gray-400">Startdatum</dt>
                            <dd class="mt-1 text-sm text-gray-900 dark:text-white">{{ $inquiry->start_date->format('d.m.Y') }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs font-medium text-gray-500 dark:text-gray-400">Enddatum</dt>
                            <dd class="mt-1 text-sm text-gray-900 dark:text-white">{{ $inquiry->end_date->format('d.m.Y') }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs font-medium text-gray-500 dark:text-gray-400">Dauer</dt>
                            <dd class="mt-1 text-sm text-gray-900 dark:text-white">
                                {{ $inquiry->start_date->diffInMonths($inquiry->end_date) + 1 }}
                                {{ $inquiry->start_date->diffInMonths($inquiry->end_date) + 1 === 1 ? 'Monat' : 'Monate' }}
                            </dd>
                        </div>
                    </dl>
                </div>
            </div>

            {{-- Message --}}
            @if($inquiry->message)
                <div class="rounded-lg bg-white dark:bg-gray-800 shadow-sm ring-1 ring-gray-950/5 dark:ring-white/10 p-6">
                    <h3 class="text-base font-semibold text-gray-900 dark:text-white mb-3 flex items-center gap-2">
                        <svg class="w-5 h-5 text-primary-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z"></path>
                        </svg>
                        Ihre Nachricht
                    </h3>
                    <p class="text-sm text-gray-700 dark:text-gray-300 whitespace-pre-wrap">{{ $inquiry->message }}</p>
                </div>
            @endif

            {{-- Selected Fields --}}
            <div class="rounded-lg bg-white dark:bg-gray-800 shadow-sm ring-1 ring-gray-950/5 dark:ring-white/10 p-6">
                <h3 class="text-base font-semibold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
                    <svg class="w-5 h-5 text-primary-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path>
                    </svg>
                    Ausgewählte Felder
                    <span class="ml-auto text-sm font-normal text-gray-500 dark:text-gray-400">
                        {{ $inquiry->fields->count() }} {{ $inquiry->fields->count() === 1 ? 'Feld' : 'Felder' }}
                    </span>
                </h3>

                <div class="space-y-2">
                    @foreach($inquiry->fields->sortBy('id') as $field)
                        <div class="flex items-center justify-between p-3 rounded-lg bg-gray-50 dark:bg-gray-700/50">
                            <div class="flex items-center gap-3">
                                <div class="flex-shrink-0 w-10 h-10 rounded-lg bg-primary-500 text-white flex items-center justify-center font-semibold text-xs">
                                    {{ $field->getIdentifier() }}
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-gray-900 dark:text-white">
                                        {{ $field->name }}
                                    </p>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">
                                        Position: Zeile {{ $field->row }}, Spalte {{ $field->column }}
                                        @if($field->width > 1 || $field->height > 1)
                                            ({{ $field->width }}x{{ $field->height }})
                                        @endif
                                    </p>
                                </div>
                            </div>
                            <div class="text-right">
                                <p class="text-sm font-semibold text-gray-900 dark:text-white">
                                    {{ number_format($field->price_per_month, 2, ',', '.') }} €
                                </p>
                                <p class="text-xs text-gray-500 dark:text-gray-400">pro Monat</p>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- Price Summary --}}
            <div class="rounded-lg bg-primary-50 dark:bg-primary-500/10 shadow-sm ring-1 ring-primary-200 dark:ring-primary-500/20 p-6">
                <h3 class="text-base font-semibold text-gray-900 dark:text-white mb-4">Preisübersicht</h3>
                <dl class="space-y-2">
                    <div class="flex justify-between text-sm">
                        <dt class="text-gray-600 dark:text-gray-400">Preis pro Monat</dt>
                        <dd class="font-medium text-gray-900 dark:text-white">{{ number_format($this->getTotalPricePerMonth(), 2, ',', '.') }} €</dd>
                    </div>
                    <div class="flex justify-between text-sm">
                        <dt class="text-gray-600 dark:text-gray-400">Anzahl Monate</dt>
                        <dd class="font-medium text-gray-900 dark:text-white">
                            {{ $inquiry->start_date->diffInMonths($inquiry->end_date) + 1 }}
                        </dd>
                    </div>
                    <div class="border-t border-primary-200 dark:border-primary-500/20 pt-2 mt-2">
                        <div class="flex justify-between">
                            <dt class="text-base font-semibold text-gray-900 dark:text-white">Gesamtpreis</dt>
                            <dd class="text-base font-bold text-primary-600 dark:text-primary-400">
                                {{ number_format($this->getTotalPrice(), 2, ',', '.') }} €
                            </dd>
                        </div>
                    </div>
                </dl>
            </div>

            {{-- Next Steps --}}
            <div class="rounded-lg bg-gray-50 dark:bg-gray-800/50 p-6">
                <h3 class="text-base font-semibold text-gray-900 dark:text-white mb-3">Wie geht es weiter?</h3>
                <ol class="space-y-2 text-sm text-gray-700 dark:text-gray-300">
                    <li class="flex items-start gap-2">
                        <span class="flex-shrink-0 w-5 h-5 rounded-full bg-primary-100 dark:bg-primary-500/20 text-primary-600 dark:text-primary-400 flex items-center justify-center text-xs font-semibold">1</span>
                        <span>Wir prüfen Ihre Anfrage und die Verfügbarkeit der gewünschten Felder.</span>
                    </li>
                    <li class="flex items-start gap-2">
                        <span class="flex-shrink-0 w-5 h-5 rounded-full bg-primary-100 dark:bg-primary-500/20 text-primary-600 dark:text-primary-400 flex items-center justify-center text-xs font-semibold">2</span>
                        <span>Sie erhalten eine Bestätigung per E-Mail an {{ $inquiry->customer_email }}.</span>
                    </li>
                    <li class="flex items-start gap-2">
                        <span class="flex-shrink-0 w-5 h-5 rounded-full bg-primary-100 dark:bg-primary-500/20 text-primary-600 dark:text-primary-400 flex items-center justify-center text-xs font-semibold">3</span>
                        <span>Nach Genehmigung senden wir Ihnen weitere Informationen und die Mietvereinbarung zu.</span>
                    </li>
                </ol>
            </div>

            {{-- Action Buttons --}}
            <div class="flex flex-col sm:flex-row gap-3 justify-center">
                <x-filament::button
                    color="gray"
                    tag="a"
                    href="{{ route('filament.app.pages.inquiry-page') }}"
                    icon="heroicon-o-arrow-left"
                >
                    Neue Anfrage stellen
                </x-filament::button>

                <x-filament::button
                    tag="a"
                    href="{{ route('filament.app.pages.board-page') }}"
                    icon="heroicon-o-view-columns"
                >
                    Zur Fördertafel
                </x-filament::button>
            </div>
        </div>
    @else
        <div class="text-center py-12">
            <p class="text-gray-500 dark:text-gray-400">Anfrage nicht gefunden.</p>
        </div>
    @endif
</x-filament-panels::page>
