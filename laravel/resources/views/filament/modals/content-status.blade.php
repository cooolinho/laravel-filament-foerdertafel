<div class="space-y-4">
    <!-- Status Overview -->
    <div class="grid grid-cols-2 gap-4">
        <!-- Veröffentlicht Status -->
        <div class="bg-white rounded-lg p-4 border-2 {{ $content->is_published ? 'border-green-200' : 'border-gray-200' }}">
            <div class="flex items-center space-x-3">
                <div class="w-12 h-12 rounded-full {{ $content->is_published ? 'bg-green-100' : 'bg-gray-100' }} flex items-center justify-center">
                    @if($content->is_published)
                        <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                        </svg>
                    @else
                        <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"></path>
                        </svg>
                    @endif
                </div>
                <div>
                    <p class="text-sm text-gray-600">Status</p>
                    <p class="text-lg font-semibold {{ $content->is_published ? 'text-green-600' : 'text-gray-600' }}">
                        {{ $content->is_published ? 'Veröffentlicht' : 'Entwurf' }}
                    </p>
                </div>
            </div>
        </div>

        <!-- Account Type -->
        <div class="bg-white rounded-lg p-4 border-2 border-gray-200">
            <div class="flex items-center space-x-3">
                <div class="w-12 h-12 rounded-full bg-blue-100 flex items-center justify-center">
                    @if($content->is_private_person)
                        <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                        </svg>
                    @else
                        <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                        </svg>
                    @endif
                </div>
                <div>
                    <p class="text-sm text-gray-600">Kontoart</p>
                    <p class="text-lg font-semibold text-blue-600">
                        {{ $content->is_private_person ? 'Privatperson' : 'Firma' }}
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Content Completeness -->
    <div class="bg-gradient-to-r from-purple-50 to-pink-50 rounded-lg p-4 border border-purple-200">
        <h4 class="font-semibold text-gray-900 mb-3">Content-Vollständigkeit</h4>

        @php
            $fields = [
                'title' => 'Titel',
                'description' => 'Beschreibung',
                'website_url' => 'Website',
                'contact_email' => 'E-Mail',
                'contact_phone' => 'Telefon',
            ];

            if (!$content->is_private_person) {
                $fields['company_logo'] = 'Firmenlogo';
            }

            $filled = 0;
            $total = count($fields);

            foreach ($fields as $field => $label) {
                if (!empty($content->$field)) {
                    $filled++;
                }
            }

            $percentage = $total > 0 ? round(($filled / $total) * 100) : 0;
        @endphp

        <div class="mb-3">
            <div class="flex justify-between text-sm mb-1">
                <span class="text-gray-600">{{ $filled }} von {{ $total }} Feldern ausgefüllt</span>
                <span class="font-semibold text-purple-600">{{ $percentage }}%</span>
            </div>
            <div class="w-full bg-gray-200 rounded-full h-3">
                <div class="bg-gradient-to-r from-purple-500 to-pink-500 h-3 rounded-full transition-all" style="width: {{ $percentage }}%"></div>
            </div>
        </div>

        <div class="space-y-2">
            @foreach($fields as $field => $label)
                <div class="flex items-center justify-between">
                    <span class="text-sm text-gray-700">{{ $label }}</span>
                    @if(!empty($content->$field))
                        <span class="text-green-600 font-semibold text-sm">✓</span>
                    @else
                        <span class="text-gray-400 text-sm">—</span>
                    @endif
                </div>
            @endforeach
        </div>
    </div>

    <!-- Access Information -->
    <div class="bg-white rounded-lg p-4 border border-gray-200">
        <h4 class="font-semibold text-gray-900 mb-3">Zugriffs-Informationen</h4>
        <div class="space-y-2 text-sm">
            <div class="flex justify-between">
                <span class="text-gray-600">Zugangscode:</span>
                <span class="font-mono font-semibold text-gray-900">{{ $content->access_code }}</span>
            </div>

            @if($content->last_accessed_at)
                <div class="flex justify-between">
                    <span class="text-gray-600">Letzter Zugriff:</span>
                    <span class="font-semibold text-gray-900">{{ $content->last_accessed_at->format('d.m.Y H:i') }} Uhr</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-600">Vor:</span>
                    <span class="font-semibold text-gray-900">{{ $content->last_accessed_at->diffForHumans() }}</span>
                </div>
            @else
                <div class="flex justify-between">
                    <span class="text-gray-600">Letzter Zugriff:</span>
                    <span class="text-gray-500 italic">Noch nicht zugegriffen</span>
                </div>
            @endif

            <div class="flex justify-between">
                <span class="text-gray-600">Erstellt am:</span>
                <span class="font-semibold text-gray-900">{{ $content->created_at->format('d.m.Y H:i') }} Uhr</span>
            </div>

            @if($content->updated_at && $content->updated_at != $content->created_at)
                <div class="flex justify-between">
                    <span class="text-gray-600">Aktualisiert am:</span>
                    <span class="font-semibold text-gray-900">{{ $content->updated_at->format('d.m.Y H:i') }} Uhr</span>
                </div>
            @endif
        </div>
    </div>

    <!-- Rental Information -->
    <div class="bg-gray-50 rounded-lg p-4 border border-gray-200">
        <h4 class="font-semibold text-gray-900 mb-3">Mietinformationen</h4>
        <div class="space-y-2 text-sm">
            <div class="flex justify-between">
                <span class="text-gray-600">Kunde:</span>
                <span class="font-semibold text-gray-900">{{ $rental->customer->name }}</span>
            </div>

            @if($rental->customer->company_name)
                <div class="flex justify-between">
                    <span class="text-gray-600">Firma:</span>
                    <span class="font-semibold text-gray-900">{{ $rental->customer->company_name }}</span>
                </div>
            @endif

            <div class="flex justify-between">
                <span class="text-gray-600">Gemietete Felder:</span>
                <span class="font-semibold text-gray-900">{{ $rental->fields->count() }}</span>
            </div>

            <div class="flex justify-between">
                <span class="text-gray-600">Miet-Status:</span>
                <span class="px-2 py-1 rounded-full text-xs font-semibold
                    {{ $rental->status === 'active' ? 'bg-green-100 text-green-800' : '' }}
                    {{ $rental->status === 'paid' ? 'bg-blue-100 text-blue-800' : '' }}
                    {{ $rental->status === 'pending' ? 'bg-yellow-100 text-yellow-800' : '' }}
                    {{ $rental->status === 'completed' ? 'bg-gray-100 text-gray-800' : '' }}
                    {{ $rental->status === 'cancelled' ? 'bg-red-100 text-red-800' : '' }}
                ">
                    {{ $rental->getStatusLabel() }}
                </span>
            </div>
        </div>
    </div>
</div>
