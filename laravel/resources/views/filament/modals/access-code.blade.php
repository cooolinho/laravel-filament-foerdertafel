<div class="space-y-4">
    <!-- Zugangscode Card -->
    <div class="bg-gradient-to-r from-blue-50 to-purple-50 rounded-lg p-6 border-2 border-blue-200">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-semibold text-gray-900">Zugangscode</h3>
            @if($isPublished)
                <span class="px-3 py-1 bg-green-100 text-green-800 rounded-full text-sm font-semibold">
                    ✓ Veröffentlicht
                </span>
            @else
                <span class="px-3 py-1 bg-gray-100 text-gray-800 rounded-full text-sm font-semibold">
                    Entwurf
                </span>
            @endif
        </div>

        <div class="bg-white rounded-lg p-4 border-2 border-dashed border-blue-300 mb-4">
            <div class="flex items-center justify-between">
                <div class="flex-1">
                    <p class="text-sm text-gray-600 mb-1">Code für Kundenportal:</p>
                    <p class="text-2xl font-mono font-bold text-blue-600 tracking-wider">{{ $accessCode }}</p>
                </div>
                <button
                    onclick="copyToClipboard('{{ $accessCode }}')"
                    class="px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600 transition"
                >
                    Kopieren
                </button>
            </div>
        </div>

        <div class="space-y-2">
            <div class="flex items-start">
                <svg class="w-5 h-5 text-blue-500 mr-2 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <p class="text-sm text-gray-600">
                    Der Kunde kann mit diesem Code auf das Kundenportal zugreifen.
                </p>
            </div>

            @if($lastAccessed)
                <div class="flex items-start">
                    <svg class="w-5 h-5 text-green-500 mr-2 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <p class="text-sm text-gray-600">
                        Zuletzt zugegriffen: <strong>{{ $lastAccessed }}</strong>
                    </p>
                </div>
            @else
                <div class="flex items-start">
                    <svg class="w-5 h-5 text-gray-400 mr-2 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <p class="text-sm text-gray-600">
                        Noch nicht zugegriffen
                    </p>
                </div>
            @endif
        </div>
    </div>

    <!-- URL Card -->
    <div class="bg-gray-50 rounded-lg p-4 border border-gray-200">
        <p class="text-sm font-semibold text-gray-700 mb-2">Direkt-Link zum Kundenportal:</p>
        <div class="flex items-center space-x-2">
            <input
                type="text"
                value="{{ $accessUrl }}"
                readonly
                class="flex-1 px-3 py-2 bg-white border border-gray-300 rounded text-sm text-gray-700 font-mono"
                id="accessUrl"
            >
            <button
                onclick="copyToClipboard('{{ $accessUrl }}')"
                class="px-4 py-2 bg-gray-500 text-white rounded hover:bg-gray-600 transition"
            >
                URL kopieren
            </button>
            <a
                href="{{ $accessUrl }}"
                target="_blank"
                class="px-4 py-2 bg-green-500 text-white rounded hover:bg-green-600 transition"
            >
                Öffnen
            </a>
        </div>
    </div>
</div>

<script>
function copyToClipboard(text) {
    navigator.clipboard.writeText(text).then(function() {
        // Success notification
        alert('In Zwischenablage kopiert: ' + text);
    }, function(err) {
        console.error('Fehler beim Kopieren: ', err);
    });
}
</script>
