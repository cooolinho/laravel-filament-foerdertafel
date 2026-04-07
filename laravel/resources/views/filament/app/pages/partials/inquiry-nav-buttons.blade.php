{{-- Navigationsbuttons für Schritte 2–5 der Anfrage --}}
<div class="flex justify-between items-center mt-6 max-w-2xl mx-auto">
    <button type="button" wire:click="previousStep"
            class="inline-flex items-center gap-2 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-200 font-semibold py-3 px-6 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors duration-200">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
        </svg>
        Zurück
    </button>

    @php
        // wenn wir im letzten Schritt sind, soll der Button "Absenden" heißen, ansonsten "Weiter"
        // und die farbe des buttons soll sich auch ändern, damit es klar ist, dass es der letzte Schritt ist
        $label = "Weiter";
        $color = "bg-blue-600 hover:bg-blue-700";
        if ($currentStep === 6) {
            $label = "Absenden";
            $color = "bg-green-600 hover:bg-green-700";
        }
    @endphp
    <button type="submit"
            class="inline-flex items-center gap-2 {{ $color }} text-white font-semibold py-3 px-6 rounded-lg transition-colors duration-200">
        {{ $label }}
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
        </svg>
    </button>
</div>

