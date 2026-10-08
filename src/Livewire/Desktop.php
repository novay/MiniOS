<?php

namespace Novay\MiniOS\Livewire;

use Livewire\Attributes\On;
use Livewire\Attributes\Renderless;
use Livewire\Component;
use Novay\MiniOS\Facades\MiniOS;

class Desktop extends Component
{
    public ?string $desktopPath = null;

    public function mount(?string $desktopPath = null): void
    {
        $this->desktopPath = $desktopPath;
    }

    #[On('toggle-dark-mode')]
    #[Renderless]
    public function toggleDarkMode(): void
    {
        $currentTheme = os_setting()->get('appearance.theme', 'system');
        $newTheme = $currentTheme === 'dark' ? 'light' : 'dark';
        os_setting()->set('appearance.theme', $newTheme);

        $this->dispatch('os-setting-updated', [
            'category' => 'appearance',
            'key' => 'theme',
            'value' => $newTheme,
        ]);
    }

    #[On('update-dock-setting')]
    #[Renderless]
    public function updateDockSetting(string $key, mixed $value): void
    {
        os_setting()->set("dock.{$key}", $value);

        $this->dispatch('os-setting-updated', [
            'category' => 'dock',
            'key' => $key,
            'value' => $value,
        ]);
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    public function getApplicationsProperty(): array
    {
        $configApps = config('desktop.applications', []);
        $registeredApps = MiniOS::registry()->toArray();

        return array_merge($configApps, $registeredApps);
    }

    public function render()
    {
        $view = view()->exists('pages.minios.desktop')
            ? 'pages.minios.desktop'
            : 'minios::desktop';

        $layout = view()->exists('layouts.minios.app')
            ? 'layouts.minios.app'
            : 'minios::layouts.app';

        return view($view, [
            'applications' => $this->applications,
        ])->layout($layout);
    }
}
