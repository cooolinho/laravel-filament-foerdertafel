<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Feldverwaltung - Ihre Inhalte</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .preview-box {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
    </style>
</head>
<body class="bg-gray-50">
    <!-- Header -->
    <header class="bg-white shadow-md sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
            <div class="flex justify-between items-center">
                <div class="flex items-center space-x-4">
                    <div class="h-12 w-12 bg-gradient-to-r from-green-500 to-blue-500 rounded-lg flex items-center justify-center shadow-lg">
                        <i class="fas fa-th text-white text-xl"></i>
                    </div>
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900">Feldverwaltung</h1>
                        <p class="text-sm text-gray-500">Willkommen, {{ $customer->name }}</p>
                    </div>
                </div>
                <div class="text-right">
                    <div class="text-sm text-gray-500">Vermietung #{{ $rental->id }}</div>
                    <div class="text-sm font-semibold text-green-600">
                        <i class="fas fa-calendar mr-1"></i>
                        {{ $rental->start_date->format('d.m.Y') }} - {{ $rental->end_date->format('d.m.Y') }}
                    </div>
                </div>
            </div>
        </div>
    </header>

    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Success/Error Messages -->
        @if(session('success'))
            <div class="mb-6 bg-green-50 border-l-4 border-green-500 p-4 rounded-lg shadow-md animate-pulse">
                <div class="flex items-center">
                    <i class="fas fa-check-circle text-green-500 text-xl mr-3"></i>
                    <p class="text-green-700 font-semibold">{{ session('success') }}</p>
                </div>
            </div>
        @endif

        @if(session('error'))
            <div class="mb-6 bg-red-50 border-l-4 border-red-500 p-4 rounded-lg shadow-md">
                <div class="flex items-center">
                    <i class="fas fa-exclamation-circle text-red-500 text-xl mr-3"></i>
                    <p class="text-red-700 font-semibold">{{ session('error') }}</p>
                </div>
            </div>
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Main Content Form -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Info Card -->
                <div class="bg-gradient-to-r from-blue-500 to-purple-600 rounded-xl shadow-lg p-6 text-white">
                    <div class="flex items-start justify-between">
                        <div>
                            <h2 class="text-2xl font-bold mb-2">Ihre gemieteten Felder</h2>
                            <p class="text-blue-100">Sie haben {{ $fields->count() }} Feld(er) gemietet</p>
                        </div>
                        <div class="bg-white bg-opacity-20 rounded-lg px-4 py-2">
                            <div class="text-3xl font-bold">{{ $fields->count() }}</div>
                            <div class="text-xs">Felder</div>
                        </div>
                    </div>
                    <div class="mt-4 flex flex-wrap gap-2">
                        @foreach($fields as $field)
                            <span class="bg-white bg-opacity-20 px-3 py-1 rounded-full text-sm font-semibold">
                                <i class="fas fa-map-marker-alt mr-1"></i>{{ $field->name }}
                            </span>
                        @endforeach
                    </div>
                </div>

                <!-- Content Form -->
                <div class="bg-white rounded-xl shadow-lg overflow-hidden">
                    <div class="bg-gradient-to-r from-gray-700 to-gray-900 px-6 py-4">
                        <h3 class="text-xl font-bold text-white flex items-center">
                            <i class="fas fa-edit mr-2"></i>
                            Inhalte bearbeiten
                        </h3>
                    </div>

                    <form action="{{ route('rental.content.update', ['code' => $rentalContent->access_code]) }}" method="POST" enctype="multipart/form-data" class="p-6 space-y-6">
                        @csrf

                        <!-- Title -->
                        <div>
                            <label for="title" class="block text-sm font-semibold text-gray-700 mb-2">
                                <i class="fas fa-heading text-green-500 mr-2"></i>Titel / Überschrift
                            </label>
                            <input
                                type="text"
                                name="title"
                                id="title"
                                value="{{ old('title', $rentalContent->title) }}"
                                class="block w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:ring-4 focus:ring-green-500 focus:border-green-500 transition"
                                placeholder="z.B. Ihr Firmenname oder eine Überschrift"
                                maxlength="255"
                            >
                            @error('title')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Description -->
                        <div>
                            <label for="description" class="block text-sm font-semibold text-gray-700 mb-2">
                                <i class="fas fa-align-left text-blue-500 mr-2"></i>Beschreibung
                            </label>
                            <textarea
                                name="description"
                                id="description"
                                rows="6"
                                class="block w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:ring-4 focus:ring-green-500 focus:border-green-500 transition"
                                placeholder="Erzählen Sie Ihren Besuchern etwas über sich oder Ihr Angebot..."
                                maxlength="2000"
                            >{{ old('description', $rentalContent->description) }}</textarea>
                            <p class="mt-1 text-sm text-gray-500">
                                <span id="charCount">{{ strlen($rentalContent->description ?? '') }}</span>/2000 Zeichen
                            </p>
                            @error('description')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Website URL -->
                        <div>
                            <label for="website_url" class="block text-sm font-semibold text-gray-700 mb-2">
                                <i class="fas fa-globe text-purple-500 mr-2"></i>Website
                            </label>
                            <input
                                type="url"
                                name="website_url"
                                id="website_url"
                                value="{{ old('website_url', $rentalContent->website_url) }}"
                                class="block w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:ring-4 focus:ring-green-500 focus:border-green-500 transition"
                                placeholder="https://www.ihre-website.de"
                            >
                            @error('website_url')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Contact Email -->
                        <div>
                            <label for="contact_email" class="block text-sm font-semibold text-gray-700 mb-2">
                                <i class="fas fa-envelope text-red-500 mr-2"></i>Kontakt E-Mail
                            </label>
                            <input
                                type="email"
                                name="contact_email"
                                id="contact_email"
                                value="{{ old('contact_email', $rentalContent->contact_email) }}"
                                class="block w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:ring-4 focus:ring-green-500 focus:border-green-500 transition"
                                placeholder="kontakt@example.com"
                            >
                            @error('contact_email')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Contact Phone -->
                        <div>
                            <label for="contact_phone" class="block text-sm font-semibold text-gray-700 mb-2">
                                <i class="fas fa-phone text-green-500 mr-2"></i>Telefonnummer
                            </label>
                            <input
                                type="tel"
                                name="contact_phone"
                                id="contact_phone"
                                value="{{ old('contact_phone', $rentalContent->contact_phone) }}"
                                class="block w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:ring-4 focus:ring-green-500 focus:border-green-500 transition"
                                placeholder="+49 123 456789"
                            >
                            @error('contact_phone')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Logo Upload (only for companies) -->
                        @if($rentalContent->canUploadLogo())
                            <div class="border-2 border-dashed border-gray-300 rounded-lg p-6 bg-gray-50">
                                <label class="block text-sm font-semibold text-gray-700 mb-3">
                                    <i class="fas fa-image text-yellow-500 mr-2"></i>Firmenlogo
                                </label>

                                @if($rentalContent->company_logo)
                                    <div class="mb-4 flex items-center space-x-4">
                                        <img
                                            src="{{ Storage::url($rentalContent->company_logo) }}"
                                            alt="Aktuelles Logo"
                                            class="h-24 w-24 object-contain border-2 border-gray-300 rounded-lg bg-white p-2"
                                        >
                                        <div class="flex-1">
                                            <p class="text-sm text-gray-600 mb-2">Aktuelles Logo</p>
                                            <label class="flex items-center space-x-2 cursor-pointer">
                                                <input
                                                    type="checkbox"
                                                    name="delete_logo"
                                                    value="1"
                                                    class="rounded border-gray-300 text-red-600 focus:ring-red-500"
                                                >
                                                <span class="text-sm text-red-600 font-medium">Logo löschen</span>
                                            </label>
                                        </div>
                                    </div>
                                @endif

                                <input
                                    type="file"
                                    name="company_logo"
                                    id="company_logo"
                                    accept="image/*"
                                    class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-green-50 file:text-green-700 hover:file:bg-green-100"
                                >
                                <p class="mt-2 text-xs text-gray-500">
                                    PNG, JPG, GIF, SVG bis zu 2MB
                                </p>
                                @error('company_logo')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        @else
                            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                                <div class="flex items-start">
                                    <i class="fas fa-info-circle text-blue-500 text-xl mr-3 mt-1"></i>
                                    <div>
                                        <h4 class="text-sm font-semibold text-blue-900 mb-1">Logo-Upload nicht verfügbar</h4>
                                        <p class="text-sm text-blue-700">
                                            Der Logo-Upload ist nur für Firmen verfügbar. Sie sind als Privatperson registriert.
                                        </p>
                                    </div>
                                </div>
                            </div>
                        @endif

                        <!-- Publish Toggle -->
                        <div class="border-t border-gray-200 pt-6">
                            <label class="flex items-center justify-between cursor-pointer">
                                <div class="flex items-center">
                                    <div class="bg-green-100 rounded-lg p-3 mr-4">
                                        <i class="fas fa-eye text-green-600 text-xl"></i>
                                    </div>
                                    <div>
                                        <div class="text-sm font-semibold text-gray-900">Inhalte veröffentlichen</div>
                                        <div class="text-sm text-gray-500">Macht Ihre Inhalte öffentlich sichtbar</div>
                                    </div>
                                </div>
                                <div class="relative">
                                    <input
                                        type="checkbox"
                                        name="is_published"
                                        value="1"
                                        {{ old('is_published', $rentalContent->is_published) ? 'checked' : '' }}
                                        class="sr-only peer"
                                        id="is_published"
                                    >
                                    <div class="w-14 h-8 bg-gray-300 rounded-full peer peer-checked:bg-green-500 transition-colors"></div>
                                    <div class="absolute left-1 top-1 bg-white w-6 h-6 rounded-full transition-transform peer-checked:translate-x-6"></div>
                                </div>
                            </label>
                        </div>

                        <!-- Submit Button -->
                        <div class="flex space-x-4 pt-4">
                            <button
                                type="submit"
                                class="flex-1 flex justify-center items-center py-4 px-6 border border-transparent rounded-lg shadow-lg text-lg font-semibold text-white bg-gradient-to-r from-green-500 to-green-600 hover:from-green-600 hover:to-green-700 focus:outline-none focus:ring-4 focus:ring-green-500 transition transform hover:scale-105"
                            >
                                <i class="fas fa-save mr-2"></i>
                                Änderungen speichern
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="lg:col-span-1 space-y-6">
                <!-- Quick Info -->
                <div class="bg-white rounded-xl shadow-lg p-6">
                    <h3 class="text-lg font-bold text-gray-900 mb-4 flex items-center">
                        <i class="fas fa-info-circle text-blue-500 mr-2"></i>
                        Ihre Information
                    </h3>
                    <div class="space-y-3 text-sm">
                        <div class="flex items-start">
                            <i class="fas fa-user text-gray-400 mr-3 mt-1"></i>
                            <div>
                                <div class="font-semibold text-gray-700">Name</div>
                                <div class="text-gray-600">{{ $customer->name }}</div>
                            </div>
                        </div>
                        @if($customer->company_name)
                            <div class="flex items-start">
                                <i class="fas fa-building text-gray-400 mr-3 mt-1"></i>
                                <div>
                                    <div class="font-semibold text-gray-700">Firma</div>
                                    <div class="text-gray-600">{{ $customer->company_name }}</div>
                                </div>
                            </div>
                        @endif
                        <div class="flex items-start">
                            <i class="fas fa-envelope text-gray-400 mr-3 mt-1"></i>
                            <div>
                                <div class="font-semibold text-gray-700">E-Mail</div>
                                <div class="text-gray-600">{{ $customer->email }}</div>
                            </div>
                        </div>
                        <div class="flex items-start">
                            <i class="fas fa-hashtag text-gray-400 mr-3 mt-1"></i>
                            <div>
                                <div class="font-semibold text-gray-700">Zugangscode</div>
                                <div class="text-gray-600 font-mono">{{ $rentalContent->access_code }}</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Status Card -->
                <div class="bg-white rounded-xl shadow-lg p-6">
                    <h3 class="text-lg font-bold text-gray-900 mb-4 flex items-center">
                        <i class="fas fa-chart-line text-purple-500 mr-2"></i>
                        Status
                    </h3>
                    <div class="space-y-4">
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-gray-600">Veröffentlicht</span>
                            @if($rentalContent->is_published)
                                <span class="px-3 py-1 bg-green-100 text-green-800 rounded-full text-xs font-semibold">
                                    <i class="fas fa-check mr-1"></i>Ja
                                </span>
                            @else
                                <span class="px-3 py-1 bg-gray-100 text-gray-800 rounded-full text-xs font-semibold">
                                    <i class="fas fa-times mr-1"></i>Entwurf
                                </span>
                            @endif
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-gray-600">Kontoart</span>
                            <span class="px-3 py-1 bg-blue-100 text-blue-800 rounded-full text-xs font-semibold">
                                @if($rentalContent->is_private_person)
                                    <i class="fas fa-user mr-1"></i>Privat
                                @else
                                    <i class="fas fa-building mr-1"></i>Firma
                                @endif
                            </span>
                        </div>
                        @if($rentalContent->last_accessed_at)
                            <div class="pt-4 border-t border-gray-200">
                                <div class="text-xs text-gray-500">Letzter Zugriff</div>
                                <div class="text-sm font-semibold text-gray-700">
                                    {{ $rentalContent->last_accessed_at->format('d.m.Y H:i') }} Uhr
                                </div>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Help Card -->
                <div class="bg-gradient-to-br from-yellow-50 to-orange-50 rounded-xl shadow-lg p-6 border-2 border-yellow-200">
                    <h3 class="text-lg font-bold text-gray-900 mb-3 flex items-center">
                        <i class="fas fa-life-ring text-orange-500 mr-2"></i>
                        Hilfe benötigt?
                    </h3>
                    <p class="text-sm text-gray-700 mb-4">
                        Bei Fragen oder Problemen stehen wir Ihnen gerne zur Verfügung.
                    </p>
                    <a
                        href="mailto:support@example.com"
                        class="block w-full text-center py-3 px-4 bg-white border-2 border-orange-300 rounded-lg text-orange-700 font-semibold hover:bg-orange-50 transition"
                    >
                        <i class="fas fa-envelope mr-2"></i>
                        Support kontaktieren
                    </a>
                </div>
            </div>
        </div>
    </main>

    <!-- Footer -->
    <footer class="bg-white border-t border-gray-200 mt-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
            <p class="text-center text-gray-500 text-sm">
                &copy; {{ date('Y') }} Feldverwaltung. Alle Rechte vorbehalten.
            </p>
        </div>
    </footer>

    <script>
        // Character counter for description
        const textarea = document.getElementById('description');
        const charCount = document.getElementById('charCount');

        textarea.addEventListener('input', function() {
            charCount.textContent = this.value.length;
        });

        // Toggle switch animation
        document.getElementById('is_published').addEventListener('change', function() {
            if (this.checked) {
                // Show animation or message
                console.log('Content will be published');
            }
        });

        // Logo preview
        document.getElementById('company_logo')?.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file && file.type.startsWith('image/')) {
                // Could add preview functionality here
                console.log('Logo selected:', file.name);
            }
        });
    </script>
</body>
</html>
