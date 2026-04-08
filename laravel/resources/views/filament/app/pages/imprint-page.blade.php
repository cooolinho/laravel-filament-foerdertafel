<x-filament-panels::page>
    @if($this->imprintText)
        <div class="prose prose-sm max-w-none dark:prose-invert">
            {!! $this->imprintText !!}
        </div>
    @else
        <div class="text-center text-gray-500 dark:text-gray-400 py-8">
            <p>Es wurde noch kein Impressum hinterlegt.</p>
        </div>
    @endif
</x-filament-panels::page>
