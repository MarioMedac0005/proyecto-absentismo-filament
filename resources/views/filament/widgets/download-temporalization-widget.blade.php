<x-filament-widgets::widget>
    <x-filament::section
        heading="Descarga de Temporalización"
        description="Controla si los profesores pueden descargarla."
    >
        <x-filament::button
            wire:click="toggle"
            wire:loading.attr="disabled"
            :color="$enabled ? 'success' : 'danger'"
            :icon="$enabled ? 'heroicon-o-check' : 'heroicon-o-x-mark'"
        >
            {{ $enabled ? 'Habilitado' : 'Deshabilitado' }}
        </x-filament::button>
    </x-filament::section>
</x-filament-widgets::widget>