<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $document->title }} – Fördertafel</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        html, body { height: 100%; }
        #pdf-frame { min-height: 600px; }
    </style>
</head>
<body class="bg-gray-100 min-h-screen flex flex-col">

    {{-- Header --}}
    <header class="bg-white border-b border-gray-200 shadow-sm">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-4 flex items-center justify-between gap-4">
            {{-- Dokumentinfo --}}
            <div class="flex items-center gap-3 min-w-0">
                <div class="flex-shrink-0 h-10 w-10 rounded-lg bg-green-100 flex items-center justify-center">
                    <i class="fas fa-file-pdf text-green-600 text-xl"></i>
                </div>
                <div class="min-w-0">
                    <h1 class="text-lg font-semibold text-gray-900 truncate">{{ $document->title }}</h1>
                    <p class="text-sm text-gray-500 flex items-center gap-2">
                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-700">
                            {{ $document->getTypeLabel() }}
                        </span>
                        @if($document->file_size)
                            <span class="text-gray-400">·</span>
                            <span>{{ $document->getFileSizeHuman() }}</span>
                        @endif
                        @if($document->version > 1)
                            <span class="text-gray-400">·</span>
                            <span>Version {{ $document->version }}</span>
                        @endif
                    </p>
                </div>
            </div>

            {{-- Aktionen --}}
            <div class="flex items-center gap-2 flex-shrink-0">
                <a
                    href="{{ route('documents.file', $document) }}"
                    download="{{ $document->file_name ?? $document->title }}"
                    class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-green-600 hover:bg-green-700 text-white text-sm font-medium transition-colors"
                >
                    <i class="fas fa-download"></i>
                    <span class="hidden sm:inline">Herunterladen</span>
                </a>
                <a
                    href="{{ route('documents.file', $document) }}"
                    target="_blank"
                    class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-gray-200 hover:bg-gray-300 text-gray-700 text-sm font-medium transition-colors"
                    title="In neuem Tab öffnen"
                >
                    <i class="fas fa-external-link-alt"></i>
                    <span class="hidden sm:inline">Neuer Tab</span>
                </a>
            </div>
        </div>
    </header>

    {{-- Beschreibung (optional) --}}
    @if($document->description)
        <div class="bg-blue-50 border-b border-blue-100">
            <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-3 flex items-start gap-2 text-sm text-blue-800">
                <i class="fas fa-info-circle mt-0.5 flex-shrink-0"></i>
                <p>{{ $document->description }}</p>
            </div>
        </div>
    @endif

    {{-- PDF-Viewer --}}
    <main class="flex-1 flex flex-col max-w-6xl w-full mx-auto px-4 sm:px-6 lg:px-8 py-6">

        {{-- Eingebetteter PDF-Viewer --}}
        @if($document->isPdf())
            <div class="flex-1 rounded-xl overflow-hidden shadow-lg border border-gray-200 bg-white flex flex-col">
                <iframe
                    id="pdf-frame"
                    src="{{ route('documents.file', $document) }}#toolbar=1&navpanes=0&scrollbar=1&view=FitH"
                    class="w-full flex-1 border-0"
                    style="height: calc(100vh - 220px); min-height: 500px;"
                    title="{{ $document->title }}"
                ></iframe>
            </div>

            {{-- Fallback für Browser ohne PDF-Unterstützung --}}
            <noscript>
                <p class="mt-4 text-center text-gray-600">
                    Ihr Browser unterstützt keine eingebetteten PDFs.
                    <a href="{{ route('documents.file', $document) }}" class="text-green-600 underline">PDF herunterladen</a>
                </p>
            </noscript>

        @else
            {{-- Nicht-PDF-Dateien: Download-Hinweis --}}
            <div class="flex-1 flex items-center justify-center">
                <div class="text-center p-12 bg-white rounded-xl shadow border border-gray-200 max-w-md">
                    <div class="mx-auto h-16 w-16 rounded-full bg-gray-100 flex items-center justify-center mb-4">
                        <i class="fas fa-file text-gray-400 text-2xl"></i>
                    </div>
                    <h2 class="text-lg font-semibold text-gray-800 mb-2">Vorschau nicht verfügbar</h2>
                    <p class="text-gray-500 text-sm mb-6">
                        Für diesen Dateityp ({{ $document->mime_type ?? 'unbekannt' }}) ist keine Vorschau verfügbar.
                        Bitte laden Sie die Datei herunter.
                    </p>
                    <a
                        href="{{ route('documents.file', $document) }}"
                        download="{{ $document->file_name ?? $document->title }}"
                        class="inline-flex items-center gap-2 px-6 py-3 rounded-lg bg-green-600 hover:bg-green-700 text-white font-medium transition-colors"
                    >
                        <i class="fas fa-download"></i>
                        Datei herunterladen
                    </a>
                </div>
            </div>
        @endif
    </main>

    {{-- Footer --}}
    <footer class="bg-white border-t border-gray-200 mt-auto">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-4 text-center text-sm text-gray-400">
            &copy; {{ date('Y') }} Fördertafel – Alle Rechte vorbehalten.
        </div>
    </footer>

</body>
</html>

