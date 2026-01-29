<x-filament-widgets::widget>
    <x-filament::section>
        <div style="display: flex; align-items: center; justify-content: space-between; width: 100%; gap: 1rem;">
            <div style="flex: 1;">
                <h2 style="font-size: 1rem; font-weight: 700; line-height: 1.5; margin: 0;">
                    Descarga de Temporalización
                </h2>

                <p style="font-size: 0.875rem; color: #6b7280; margin-top: 0.25rem;">
                    Permite la descarga a profesores.
                </p>
            </div>

            <x-filament::button
                wire:click="toggle"
                wire:loading.attr="disabled"
                color="gray"
                outlined
                :icon="$enabled ? 'heroicon-m-check' : 'heroicon-m-x-mark'"
            >
                {{ $enabled ? 'Habilitado' : 'Deshabilitado' }}
            </x-filament::button>
        </div>
    </x-filament::section>
</x-filament-widgets::widget>