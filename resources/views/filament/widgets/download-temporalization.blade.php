<x-filament-widgets::widget>
    <x-filament::section>
        @if ($isAvailable)
            {{ $this->form }}
            
            <div style="margin-top: 1.5rem;">
                <x-filament::button 
                    wire:click="download" 
                    color="primary" 
                    class="w-full"
                >
                    Descargar Temporalización
                </x-filament::button>
            </div>
        @else
            <p>La temporalización aún no está disponible.</p>
        @endif
    </x-filament::section>
</x-filament-widgets::widget>
