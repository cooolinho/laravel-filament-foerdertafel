<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Zugangscode eingeben - Feldverwaltung</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-gradient-to-br from-green-50 via-blue-50 to-purple-50 min-h-screen">
    <div class="min-h-screen flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-md w-full">
            <!-- Logo/Header -->
            <div class="text-center mb-8">
                <div class="mx-auto h-20 w-20 bg-green-500 rounded-full flex items-center justify-center mb-4 shadow-lg">
                    <i class="fas fa-key text-white text-3xl"></i>
                </div>
                <h1 class="text-4xl font-bold text-gray-900 mb-2">Willkommen zurück!</h1>
                <p class="text-gray-600">Geben Sie Ihren Zugangscode ein, um Ihre Felder zu verwalten</p>
            </div>

            <!-- Main Card -->
            <div class="bg-white rounded-2xl shadow-xl overflow-hidden">
                <div class="px-8 py-10">
                    <!-- Error Messages -->
                    @if(session('error'))
                        <div class="mb-6 bg-red-50 border-l-4 border-red-500 p-4 rounded-md animate-pulse">
                            <div class="flex items-center">
                                <i class="fas fa-exclamation-circle text-red-500 mr-3"></i>
                                <p class="text-red-700">{{ session('error') }}</p>
                            </div>
                        </div>
                    @endif

                    @if(session('success'))
                        <div class="mb-6 bg-green-50 border-l-4 border-green-500 p-4 rounded-md">
                            <div class="flex items-center">
                                <i class="fas fa-check-circle text-green-500 mr-3"></i>
                                <p class="text-green-700">{{ session('success') }}</p>
                            </div>
                        </div>
                    @endif

                    <!-- Form -->
                    <form action="{{ route('rental.content.access') }}" method="POST" class="space-y-6">
                        @csrf

                        <div>
                            <label for="access_code" class="block text-sm font-semibold text-gray-700 mb-2">
                                Zugangscode
                            </label>
                            <div class="relative">
                                <input
                                    type="text"
                                    name="access_code"
                                    id="access_code"
                                    placeholder="XXXX-XXXX-XXXX"
                                    maxlength="14"
                                    class="block w-full px-4 py-4 text-center text-xl font-mono tracking-wider border-2 border-gray-300 rounded-lg focus:ring-4 focus:ring-green-500 focus:border-green-500 transition duration-200 uppercase"
                                    required
                                    autofocus
                                    oninput="this.value = this.value.toUpperCase()"
                                >
                                <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                    <i class="fas fa-lock text-gray-400"></i>
                                </div>
                            </div>
                            <p class="mt-2 text-sm text-gray-500">
                                <i class="fas fa-info-circle mr-1"></i>
                                Format: XXXX-XXXX-XXXX (mit Bindestrichen)
                            </p>
                        </div>

                        <button
                            type="submit"
                            class="w-full flex justify-center items-center py-4 px-4 border border-transparent rounded-lg shadow-lg text-lg font-semibold text-white bg-gradient-to-r from-green-500 to-green-600 hover:from-green-600 hover:to-green-700 focus:outline-none focus:ring-4 focus:ring-green-500 focus:ring-offset-2 transition duration-200 transform hover:scale-105"
                        >
                            <i class="fas fa-sign-in-alt mr-2"></i>
                            Zugriff erhalten
                        </button>
                    </form>
                </div>

                <!-- Info Section -->
                <div class="bg-gray-50 px-8 py-6 border-t border-gray-200">
                    <h3 class="text-sm font-semibold text-gray-700 mb-3 flex items-center">
                        <i class="fas fa-question-circle mr-2 text-green-500"></i>
                        Wie funktioniert das?
                    </h3>
                    <ul class="space-y-2 text-sm text-gray-600">
                        <li class="flex items-start">
                            <i class="fas fa-check text-green-500 mr-2 mt-1"></i>
                            <span>Sie haben den Zugangscode per E-Mail erhalten</span>
                        </li>
                        <li class="flex items-start">
                            <i class="fas fa-check text-green-500 mr-2 mt-1"></i>
                            <span>Geben Sie den Code im Format XXXX-XXXX-XXXX ein</span>
                        </li>
                        <li class="flex items-start">
                            <i class="fas fa-check text-green-500 mr-2 mt-1"></i>
                            <span>Verwalten Sie Ihre Feldinhalte direkt online</span>
                        </li>
                    </ul>
                </div>

                <!-- Help Section -->
                <div class="bg-blue-50 px-8 py-6 border-t border-blue-100">
                    <div class="flex items-start">
                        <i class="fas fa-envelope text-blue-500 text-xl mr-3 mt-1"></i>
                        <div>
                            <h4 class="text-sm font-semibold text-blue-900 mb-1">Keinen Zugangscode erhalten?</h4>
                            <p class="text-sm text-blue-700">
                                Prüfen Sie bitte Ihren Spam-Ordner oder kontaktieren Sie uns, wenn Sie Ihren Zugangscode nicht finden können.
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Footer -->
            <div class="text-center mt-8 text-gray-500 text-sm">
                <p>&copy; {{ date('Y') }} Feldverwaltung. Alle Rechte vorbehalten.</p>
            </div>
        </div>
    </div>

    <script>
        // Auto-format input with dashes
        document.getElementById('access_code').addEventListener('input', function(e) {
            let value = e.target.value.replace(/[^A-Z0-9]/g, '');
            if (value.length > 4) {
                value = value.substr(0, 4) + '-' + value.substr(4);
            }
            if (value.length > 9) {
                value = value.substr(0, 9) + '-' + value.substr(9);
            }
            e.target.value = value.substr(0, 14);
        });
    </script>
</body>
</html>
