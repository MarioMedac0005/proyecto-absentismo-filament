<?php

namespace App\Filament\Widgets;

use App\Models\TemporalizationSetting;
use Filament\Notifications\Notification;
use Filament\Widgets\Widget;

class SettingsTemporalizationWidget extends Widget
{
    protected string $view = 'filament.widgets.settings-temporalization-widget';

    public $enabled = false;

    public function mount(): void
    {
        $this->enabled = (bool) TemporalizationSetting::firstWhere(
            'key',
            'download_enabled'
        )?->value;
    }

    public function toggle(): void
    {
        $this->enabled = ! $this->enabled;

        TemporalizationSetting::updateOrCreate(
            ['key' => 'download_enabled'],
            ['value' => $this->enabled]
        );

        Notification::make()
            ->title('Configuración actualizada')
            ->success()
            ->send();
    }

    public static function canView(): bool
    {
        return auth()->user()?->hasRole('admin');
    }
}
