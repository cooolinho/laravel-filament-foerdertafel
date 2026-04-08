@php


    $fieldW  = $this->getFieldWidth();
    $fieldH  = $this->getFieldHeight();
    $gap     = $this->getFieldGap();
    $maxRows = $this->getMaxRows();
    $maxCols = $this->getMaxCols();
    $rects   = $this->getValidRectangles();

    // Skalierung für das SVG-Diagramm (px pro cm)
    $scale  = 60;
    $tileW  = $fieldW * $scale;
    $tileH  = $fieldH * $scale;
    $gapPx  = $gap * $scale;

    $marginLeft   = 80;
    $marginTop    = 55;
    $marginRight  = ($maxCols > 1 ? ($maxCols - 1) * 35 + 20 : 20);
    $marginBottom = ($maxRows > 1 ? ($maxRows - 1) * 22 + 20 : 20);

    $gridW = $maxCols * $tileW + ($maxCols - 1) * $gapPx;
    $gridH = $maxRows * $tileH + ($maxRows - 1) * $gapPx;

    $svgW = $gridW + $marginLeft + $marginRight;
    $svgH = $gridH + $marginTop  + $marginBottom;

    // Kachel-Koordinate (0-basierter Index)
    $tileX = fn(int $c) => $marginLeft + $c * ($tileW + $gapPx);
    $tileY = fn(int $r) => $marginTop  + $r * ($tileH + $gapPx);
@endphp

<x-filament-panels::page>

    <div class="space-y-8">

        {{-- Einführungstext --}}
        <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-4">
            <div class="flex items-start gap-3">
                <x-heroicon-o-information-circle class="w-6 h-6 text-blue-600 dark:text-blue-400 shrink-0 mt-0.5"/>
                <div>
                    <h3 class="font-semibold text-blue-900 dark:text-blue-100">Über die Feldgrößen</h3>
                    <p class="mt-1 text-sm text-blue-800 dark:text-blue-200">
                        Jede Kachel auf der Fördertafel hat eine feste physische Größe.
                        Wenn Sie mehrere Kacheln auswählen, addieren sich die Maße entsprechend –
                        zuzüglich des Abstands zwischen den Kacheln.
                        Die Auswahl muss immer ein zusammenhängendes Rechteck ergeben
                        (maximal <strong>{{ $maxRows }} Zeile(n) × {{ $maxCols }} Spalte(n)</strong>).
                    </p>
                </div>
            </div>
        </div>

        {{-- SVG-Diagramm --}}
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-1">
                Feldgrößen – Fördertafel
            </h2>
            <p class="text-sm text-gray-500 dark:text-gray-400 mb-6">Alle Angaben in cm</p>

            <div class="overflow-x-auto">
                <svg
                    width="{{ round($svgW) }}"
                    height="{{ round($svgH) }}"
                    viewBox="0 0 {{ round($svgW) }} {{ round($svgH) }}"
                    xmlns="http://www.w3.org/2000/svg"
                    class="max-w-full"
                    style="font-family: system-ui, sans-serif;">

                    {{-- Kacheln --}}
                    @for ($r = 0; $r < $maxRows; $r++)
                        @for ($c = 0; $c < $maxCols; $c++)
                            <rect
                                x="{{ $tileX($c) }}"
                                y="{{ $tileY($r) }}"
                                width="{{ $tileW }}"
                                height="{{ $tileH }}"
                                fill="#2d6a2d"
                                rx="3"/>
                        @endfor
                    @endfor

                    {{-- Spaltenbreiten oben (einzeln) --}}
                    @for ($c = 0; $c < $maxCols; $c++)
                        @php
                            $x1 = $tileX($c);
                            $x2 = $tileX($c) + $tileW;
                            $cy = $marginTop - 18;
                        @endphp
                        <line x1="{{ $x1 }}" y1="{{ $marginTop - 8 }}" x2="{{ $x2 }}" y2="{{ $marginTop - 8 }}" stroke="#374151" stroke-width="1"/>
                        <line x1="{{ $x1 }}" y1="{{ $marginTop - 12 }}" x2="{{ $x1 }}" y2="{{ $marginTop - 4 }}" stroke="#374151" stroke-width="1"/>
                        <line x1="{{ $x2 }}" y1="{{ $marginTop - 12 }}" x2="{{ $x2 }}" y2="{{ $marginTop - 4 }}" stroke="#374151" stroke-width="1"/>
                        <text x="{{ ($x1 + $x2) / 2 }}" y="{{ $cy }}" text-anchor="middle" font-size="12" fill="#374151">
                            {{ number_format($fieldW, 1, ',', '.') }}
                        </text>
                    @endfor

                    {{-- Gap-Beschriftungen horizontal oben --}}
                    @for ($c = 0; $c < $maxCols - 1; $c++)
                        @php
                            $gx1   = $tileX($c) + $tileW;
                            $gx2   = $tileX($c + 1);
                            $gmidX = ($gx1 + $gx2) / 2;
                        @endphp
                        <line x1="{{ $gx1 }}" y1="{{ $marginTop - 8 }}" x2="{{ $gx2 }}" y2="{{ $marginTop - 8 }}" stroke="#9ca3af" stroke-width="1" stroke-dasharray="2,2"/>
                        <text x="{{ $gmidX }}" y="{{ $marginTop - 18 }}" text-anchor="middle" font-size="11" fill="#6b7280">
                            {{ number_format($gap, 1, ',', '.') }}
                        </text>
                    @endfor

                    {{-- Zeilenhöhen links (einzeln) --}}
                    @for ($r = 0; $r < $maxRows; $r++)
                        @php
                            $y1 = $tileY($r);
                            $y2 = $tileY($r) + $tileH;
                            $rx = $marginLeft - 16;
                        @endphp
                        <line x1="{{ $marginLeft - 8 }}" y1="{{ $y1 }}" x2="{{ $marginLeft - 8 }}" y2="{{ $y2 }}" stroke="#374151" stroke-width="1"/>
                        <line x1="{{ $marginLeft - 12 }}" y1="{{ $y1 }}" x2="{{ $marginLeft - 4 }}" y2="{{ $y1 }}" stroke="#374151" stroke-width="1"/>
                        <line x1="{{ $marginLeft - 12 }}" y1="{{ $y2 }}" x2="{{ $marginLeft - 4 }}" y2="{{ $y2 }}" stroke="#374151" stroke-width="1"/>
                        <text x="{{ $rx }}" y="{{ ($y1 + $y2) / 2 + 4 }}" text-anchor="end" font-size="12" fill="#374151">
                            {{ number_format($fieldH, 1, ',', '.') }}
                        </text>
                    @endfor

                    {{-- Gap-Beschriftungen vertikal links --}}
                    @for ($r = 0; $r < $maxRows - 1; $r++)
                        @php
                            $gy1   = $tileY($r) + $tileH;
                            $gy2   = $tileY($r + 1);
                            $gmidY = ($gy1 + $gy2) / 2;
                        @endphp
                        <line x1="{{ $marginLeft - 8 }}" y1="{{ $gy1 }}" x2="{{ $marginLeft - 8 }}" y2="{{ $gy2 }}" stroke="#9ca3af" stroke-width="1" stroke-dasharray="2,2"/>
                        <text x="{{ $marginLeft - 16 }}" y="{{ $gmidY + 4 }}" text-anchor="end" font-size="11" fill="#6b7280">
                            {{ number_format($gap, 1, ',', '.') }}
                        </text>
                    @endfor

                    {{-- Kumulative Breitenmaße unten (für 2..maxCols Spalten) --}}
                    @for ($c = 2; $c <= $maxCols; $c++)
                        @php
                            $totalW  = app(\App\Settings\GeneralSettings::class)->calculatePhysicalWidth($c);
                            $endX    = $tileX($c - 1) + $tileW;
                            $lineY   = $gridH + $marginTop + ($c - 1) * 22;
                        @endphp
                        <line x1="{{ $marginLeft }}" y1="{{ $lineY }}" x2="{{ $endX }}" y2="{{ $lineY }}" stroke="#374151" stroke-width="1" stroke-dasharray="4,3"/>
                        <line x1="{{ $marginLeft }}" y1="{{ $lineY - 4 }}" x2="{{ $marginLeft }}" y2="{{ $lineY + 4 }}" stroke="#374151" stroke-width="1"/>
                        <line x1="{{ $endX }}" y1="{{ $lineY - 4 }}" x2="{{ $endX }}" y2="{{ $lineY + 4 }}" stroke="#374151" stroke-width="1"/>
                        <text x="{{ ($marginLeft + $endX) / 2 }}" y="{{ $lineY - 3 }}" text-anchor="middle" font-size="12" fill="#374151">
                            {{ number_format($totalW, 1, ',', '.') }}
                        </text>
                    @endfor

                    {{-- Kumulative Höhenmaße rechts (für 2..maxRows Zeilen) --}}
                    @for ($r = 2; $r <= $maxRows; $r++)
                        @php
                            $totalH  = app(\App\Settings\GeneralSettings::class)->calculatePhysicalHeight($r);
                            $endY    = $tileY($r - 1) + $tileH;
                            $lineX   = $gridW + $marginLeft + ($r - 1) * 30;
                            $midY    = ($marginTop + $endY) / 2;
                        @endphp
                        <line x1="{{ $lineX }}" y1="{{ $marginTop }}" x2="{{ $lineX }}" y2="{{ $endY }}" stroke="#374151" stroke-width="1" stroke-dasharray="4,3"/>
                        <line x1="{{ $lineX - 4 }}" y1="{{ $marginTop }}" x2="{{ $lineX + 4 }}" y2="{{ $marginTop }}" stroke="#374151" stroke-width="1"/>
                        <line x1="{{ $lineX - 4 }}" y1="{{ $endY }}" x2="{{ $lineX + 4 }}" y2="{{ $endY }}" stroke="#374151" stroke-width="1"/>
                        <text
                            x="{{ $lineX + 4 }}"
                            y="{{ $midY }}"
                            text-anchor="middle"
                            font-size="12"
                            fill="#374151"
                            transform="rotate(-90 {{ $lineX + 4 }} {{ $midY }})">
                            {{ number_format($totalH, 1, ',', '.') }}
                        </text>
                    @endfor

                </svg>
            </div>
        </div>

        {{-- Tabelle aller gültigen Konfigurationen --}}
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">
                Mögliche Auswahl-Konfigurationen
            </h2>
            <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">
                Alle Rechteck-Kombinationen, die auf der Anfrage-Seite ausgewählt werden können,
                mit den jeweiligen physischen Abmessungen.
            </p>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700 text-sm">
                    <thead class="bg-gray-50 dark:bg-gray-700">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Zeilen × Spalten</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Felder gesamt</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Breite (cm)</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Höhe (cm)</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider hidden sm:table-cell">Berechnung Breite</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider hidden sm:table-cell">Berechnung Höhe</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                        @foreach($rects as $i => $rect)
                            <tr class="{{ $i % 2 === 0 ? '' : 'bg-gray-50 dark:bg-gray-700/40' }}">
                                <td class="px-4 py-3 font-semibold text-gray-900 dark:text-white whitespace-nowrap">
                                    {{ $rect['rows'] }} × {{ $rect['cols'] }}
                                </td>
                                <td class="px-4 py-3 text-gray-700 dark:text-gray-300">{{ $rect['total'] }}</td>
                                <td class="px-4 py-3 font-semibold text-green-700 dark:text-green-400">
                                    {{ number_format($rect['width_cm'], 2, ',', '.') }} cm
                                </td>
                                <td class="px-4 py-3 font-semibold text-green-700 dark:text-green-400">
                                    {{ number_format($rect['height_cm'], 2, ',', '.') }} cm
                                </td>
                                <td class="px-4 py-3 text-gray-500 dark:text-gray-400 whitespace-nowrap hidden sm:table-cell">
                                    @if($rect['cols'] === 1)
                                        {{ number_format($fieldW, 1, ',', '.') }} cm
                                    @else
                                        {{ $rect['cols'] }} × {{ number_format($fieldW, 1, ',', '.') }} + {{ $rect['cols'] - 1 }} × {{ number_format($gap, 1, ',', '.') }}
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-gray-500 dark:text-gray-400 whitespace-nowrap hidden sm:table-cell">
                                    @if($rect['rows'] === 1)
                                        {{ number_format($fieldH, 1, ',', '.') }} cm
                                    @else
                                        {{ $rect['rows'] }} × {{ number_format($fieldH, 1, ',', '.') }} + {{ $rect['rows'] - 1 }} × {{ number_format($gap, 1, ',', '.') }}
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Einzel-Kachel Referenz --}}
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Referenz – Einzelne Kachel</h2>
            <dl class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4 text-center">
                    <dt class="text-sm text-gray-500 dark:text-gray-400">Kachelbreite</dt>
                    <dd class="mt-1 text-2xl font-bold text-gray-900 dark:text-white">{{ number_format($fieldW, 1, ',', '.') }} cm</dd>
                </div>
                <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4 text-center">
                    <dt class="text-sm text-gray-500 dark:text-gray-400">Kachelhöhe</dt>
                    <dd class="mt-1 text-2xl font-bold text-gray-900 dark:text-white">{{ number_format($fieldH, 1, ',', '.') }} cm</dd>
                </div>
                <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4 text-center">
                    <dt class="text-sm text-gray-500 dark:text-gray-400">Abstand zwischen Kacheln</dt>
                    <dd class="mt-1 text-2xl font-bold text-gray-900 dark:text-white">{{ number_format($gap, 1, ',', '.') }} cm</dd>
                </div>
            </dl>
        </div>

    </div>
</x-filament-panels::page>
