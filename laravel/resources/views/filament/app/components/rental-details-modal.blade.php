@php
    use Illuminate\Support\Facades\Storage;
    $content = $rental->content;
    $hasPublishedContent = $content
        && $content->{\App\Models\RentalContent::is_published}
        && !$content->{\App\Models\RentalContent::needs_review};
    $hasPendingReview = $content && $content->{\App\Models\RentalContent::needs_review};

    $displayName = $hasPublishedContent && $content->{\App\Models\RentalContent::title}
        ? $content->{\App\Models\RentalContent::title}
        : $rental->customer->{\App\Models\Customer::name};
@endphp

<div class="-mx-6 -my-6">

    {{-- ═══ HERO / IDENTITÄT ═══ --}}
    @if($hasPublishedContent)
        <div class="relative bg-gradient-to-br from-primary-600 via-primary-500 to-indigo-600 px-6 pt-8 pb-6 text-white overflow-hidden">
            {{-- Dekorative Kreise --}}
            <div class="absolute top-0 right-0 w-48 h-48 bg-white/5 rounded-full -translate-y-1/2 translate-x-1/4 pointer-events-none"></div>
            <div class="absolute bottom-0 left-0 w-32 h-32 bg-white/5 rounded-full translate-y-1/2 -translate-x-1/4 pointer-events-none"></div>

            <div class="relative flex items-start gap-4">
                {{-- Logo oder Icon --}}
                @if($content->{\App\Models\RentalContent::company_logo} && !$content->{\App\Models\RentalContent::is_private_person})
                    <div class="flex-shrink-0 w-20 h-20 rounded-2xl bg-white shadow-lg overflow-hidden p-1.5">
                        <img
                            src="{{ Storage::disk('public')->url($content->{\App\Models\RentalContent::company_logo}) }}"
                            alt="{{ $displayName }}"
                            class="w-full h-full object-contain"
                        >
                    </div>
                @else
                    <div class="flex-shrink-0 w-16 h-16 rounded-2xl bg-white/20 flex items-center justify-center">
                        @if($content->{\App\Models\RentalContent::is_private_person})
                            <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                            </svg>
                        @else
                            <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                            </svg>
                        @endif
                    </div>
                @endif

                <div class="flex-1 min-w-0">
                    <h2 class="text-2xl font-bold leading-tight truncate">{{ $displayName }}</h2>
                    @if($content->{\App\Models\RentalContent::description})
                        <p class="mt-2 text-primary-100 text-sm leading-relaxed line-clamp-3">
                            {{ $content->{\App\Models\RentalContent::description} }}
                        </p>
                    @endif
                </div>
            </div>

            {{-- Kontakt-Chips --}}
            @if($content->{\App\Models\RentalContent::website_url} || $content->{\App\Models\RentalContent::contact_email} || $content->{\App\Models\RentalContent::contact_phone})
                <div class="relative mt-5 flex flex-wrap gap-2">
                    @if($content->{\App\Models\RentalContent::website_url})
                        <a href="{{ $content->{\App\Models\RentalContent::website_url} }}" target="_blank"
                           class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full bg-white/15 hover:bg-white/25 text-white text-xs font-medium transition-colors">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                            </svg>
                            {{ parse_url($content->{\App\Models\RentalContent::website_url}, PHP_URL_HOST) }}
                        </a>
                    @endif
                    @if($content->{\App\Models\RentalContent::contact_email})
                        <a href="mailto:{{ $content->{\App\Models\RentalContent::contact_email} }}"
                           class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full bg-white/15 hover:bg-white/25 text-white text-xs font-medium transition-colors">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                            </svg>
                            {{ $content->{\App\Models\RentalContent::contact_email} }}
                        </a>
                    @endif
                    @if($content->{\App\Models\RentalContent::contact_phone})
                        <a href="tel:{{ $content->{\App\Models\RentalContent::contact_phone} }}"
                           class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full bg-white/15 hover:bg-white/25 text-white text-xs font-medium transition-colors">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.948V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 8V5z"/>
                            </svg>
                            {{ $content->{\App\Models\RentalContent::contact_phone} }}
                        </a>
                    @endif
                </div>
            @endif
        </div>

    @else
        {{-- Kein veröffentlichter Content: einfacher Header --}}
        <div class="relative bg-gradient-to-br from-gray-700 to-gray-900 px-6 pt-8 pb-6 text-white overflow-hidden">
            <div class="absolute top-0 right-0 w-40 h-40 bg-white/5 rounded-full -translate-y-1/2 translate-x-1/4 pointer-events-none"></div>

            <div class="relative flex items-center gap-4">
                <div class="flex-shrink-0 w-14 h-14 rounded-2xl bg-white/15 flex items-center justify-center">
                    <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                    </svg>
                </div>
                <div>
                    <h2 class="text-xl font-bold">{{ $rental->customer->{\App\Models\Customer::name} }}</h2>
                    @if($rental->customer->{\App\Models\Customer::company_name})
                        <p class="text-gray-300 text-sm mt-0.5">{{ $rental->customer->{\App\Models\Customer::company_name} }}</p>
                    @endif
                    <div class="mt-2 flex flex-wrap gap-2">
                        @if($rental->customer->{\App\Models\Customer::email})
                            <a href="mailto:{{ $rental->customer->{\App\Models\Customer::email} }}"
                               class="inline-flex items-center gap-1 text-xs text-gray-300 hover:text-white transition-colors">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                </svg>
                                {{ $rental->customer->{\App\Models\Customer::email} }}
                            </a>
                        @endif
                        @if($rental->customer->{\App\Models\Customer::phone})
                            <a href="tel:{{ $rental->customer->{\App\Models\Customer::phone} }}"
                               class="inline-flex items-center gap-1 text-xs text-gray-300 hover:text-white transition-colors">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.948V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 8V5z"/>
                                </svg>
                                {{ $rental->customer->{\App\Models\Customer::phone} }}
                            </a>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Hinweis: Prüfung ausstehend --}}
            @if($hasPendingReview)
                <div class="relative mt-4 flex items-center gap-2 bg-amber-400/20 border border-amber-400/30 rounded-xl px-4 py-2.5 text-amber-200 text-xs">
                    <svg class="w-4 h-4 flex-shrink-0 text-amber-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    Dieser Mieter hat Inhalte eingereicht – Prüfung durch Admin ausstehend.
                </div>
            @endif
        </div>
    @endif

    {{-- ═══ INHALT ═══ --}}
    <div class="px-6 py-5 space-y-5">

        {{-- Mietdauer --}}
        <div>
            <h3 class="text-xs font-semibold text-gray-400 dark:text-gray-500 uppercase tracking-wider mb-3 flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
                Mietdauer
            </h3>
            <div class="grid grid-cols-3 gap-3">
                <div class="bg-gray-50 dark:bg-gray-800 rounded-xl p-3 text-center border border-gray-100 dark:border-gray-700">
                    <p class="text-xs text-gray-500 dark:text-gray-400 mb-1">Von</p>
                    <p class="text-sm font-semibold text-gray-900 dark:text-white">{{ $rental->{\App\Models\Rental::start_date}->format('d.m.Y') }}</p>
                </div>
                <div class="bg-gray-50 dark:bg-gray-800 rounded-xl p-3 text-center border border-gray-100 dark:border-gray-700">
                    <p class="text-xs text-gray-500 dark:text-gray-400 mb-1">Bis</p>
                    <p class="text-sm font-semibold text-gray-900 dark:text-white">{{ $rental->{\App\Models\Rental::end_date}->format('d.m.Y') }}</p>
                </div>
                <div class="bg-primary-50 dark:bg-primary-500/10 rounded-xl p-3 text-center border border-primary-100 dark:border-primary-500/20">
                    <p class="text-xs text-primary-600 dark:text-primary-400 mb-1">Dauer</p>
                    @php $months = $rental->rental_months; @endphp
                    <p class="text-sm font-semibold text-primary-700 dark:text-primary-300">{{ $months }} {{ $months === 1 ? 'Monat' : 'Monate' }}</p>
                </div>
            </div>
        </div>

        {{-- Gemietete Felder --}}
        <div>
            <h3 class="text-xs font-semibold text-gray-400 dark:text-gray-500 uppercase tracking-wider mb-3 flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/>
                </svg>
                Gemietete Felder
                <span class="ml-auto bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300 text-xs font-medium px-2 py-0.5 rounded-full">
                    {{ $rental->fields->count() }}
                </span>
            </h3>
            <div class="space-y-2">
                @foreach($rental->fields->sortBy(\App\Models\Field::row) as $field)
                    <div class="flex items-center justify-between p-3 rounded-xl bg-gray-50 dark:bg-gray-800 border border-gray-100 dark:border-gray-700 hover:border-gray-200 dark:hover:border-gray-600 transition-colors">
                        <div class="flex items-center gap-3">
                            <div class="flex-shrink-0 w-9 h-9 rounded-lg bg-primary-500 text-white flex items-center justify-center font-bold text-xs shadow-sm">
                                {{ $field->getIdentifier() }}
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-900 dark:text-white">{{ $field->{\App\Models\Field::name} }}</p>
                                <p class="text-xs text-gray-500 dark:text-gray-400">
                                    Reihe {{ $field->{\App\Models\Field::row} }}, Spalte {{ $field->{\App\Models\Field::column} }}
                                    @if($field->{\App\Models\Field::width} > 1 || $field->{\App\Models\Field::height} > 1)
                                        &middot; {{ $field->{\App\Models\Field::width} }}×{{ $field->{\App\Models\Field::height} }}
                                    @endif
                                </p>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        {{-- Status --}}
        @php
            $statusConfig = [
                \App\Models\Rental::STATUS_ACTIVE    => ['bg-emerald-100 text-emerald-800 dark:bg-emerald-900/40 dark:text-emerald-300', 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z'],
                \App\Models\Rental::STATUS_COMPLETED => ['bg-blue-100 text-blue-800 dark:bg-blue-900/40 dark:text-blue-300', 'M5 13l4 4L19 7'],
                \App\Models\Rental::STATUS_CANCELLED => ['bg-red-100 text-red-800 dark:bg-red-900/40 dark:text-red-300', 'M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z'],
                \App\Models\Rental::STATUS_PAID      => ['bg-violet-100 text-violet-800 dark:bg-violet-900/40 dark:text-violet-300', 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4'],
            ];
            $currentStatus = $rental->{\App\Models\Rental::status};
            [$statusClass, $statusIcon] = $statusConfig[$currentStatus] ?? ['bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300', 'M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z'];
        @endphp
        <div class="flex justify-center pb-1">
            <span class="inline-flex items-center gap-2 px-4 py-2 rounded-full text-sm font-semibold {{ $statusClass }}">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $statusIcon }}"/>
                </svg>
                {{ $rental->getStatusLabel() }}
            </span>
        </div>

    </div>
</div>
