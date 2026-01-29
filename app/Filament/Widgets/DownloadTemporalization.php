<?php

namespace App\Filament\Widgets;

use App\Models\Course;
use App\Models\TemporalizationSetting;
use App\Services\TemporalizationCalendar;
use Filament\Actions\ButtonAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Schemas\Schema;
use Filament\Notifications\Notification;
use Filament\Widgets\Widget;

class DownloadTemporalization extends Widget implements HasForms
{
    use InteractsWithForms;

    protected string $view = 'filament.widgets.download-temporalization';

    public $isAvailable = false;
    public ?array $data = [];

    public function mount(): void
    {
        $this->checkAvailability();

        // Inicializar el formulario con datos por defecto
        $this->form->fill();
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components($this->getFormSchema())
            ->statePath('data');
    }

    protected function getFormSchema(): array
    {
        return [
            Select::make('course_id')
                ->label('Selecciona Curso')
                ->options(function () {
                    $user = auth()->user();
                    if ($user->hasRole('profesor')) {
                        // Get courses related to the professor's subjects
                        $courseIds = $user->subjects()->pluck('course_id')->unique();
                        return Course::whereIn('id', $courseIds)->pluck('nombre', 'id')->toArray();
                    }
                    
                    return Course::pluck('nombre', 'id')->toArray();
                })
                ->required(),
        ];
    }

    public function checkAvailability()
    {
        $setting = TemporalizationSetting::where('key', 'download_enabled')->first();
        $this->isAvailable = $setting ? (bool) $setting->value : false;
    }

    public function download(TemporalizationCalendar $service)
    {
        $data = $this->form->getState();

        if (!isset($data['course_id'])) {
            Notification::make()
                ->title('Error')
                ->body('Debes seleccionar un curso')
                ->danger()
                ->send();
            return;
        }

        try {
            $course = Course::findOrFail($data['course_id']);
            return $service->generatePdf($course);
        } catch (\Exception $e) {
            Notification::make()
                ->title('Error al generar PDF')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }
    public static function canView(): bool
    {
        return ! auth()->user()->hasRole('admin');
    }
}
