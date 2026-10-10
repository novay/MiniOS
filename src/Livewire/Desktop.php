<?php

namespace Novay\MiniOS\Livewire;

use Livewire\Attributes\On;
use Livewire\Attributes\Renderless;
use Livewire\Component;
use Novay\MiniOS\Concerns\HasNotifications;
use Novay\MiniOS\Facades\MiniOS;
use Novay\MiniOS\Services\TrashService;

class Desktop extends Component
{
    use HasNotifications;

    public ?string $desktopPath = null;

    public int $trashCount = 0;

    public function mount(?string $desktopPath = null): void
    {
        $this->desktopPath = $desktopPath;
        $this->trashCount = app(TrashService::class)->getTrashCount();
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

    #[On('trash-updated')]
    public function onTrashUpdated(mixed $count = null): void
    {
        if (is_array($count)) {
            $count = $count['count'] ?? null;
        }

        $this->trashCount = is_numeric($count) ? (int) $count : app(TrashService::class)->getTrashCount();
    }

    #[On('empty-trash')]
    public function emptyTrash(): void
    {
        /** @var TrashService $trash */
        $trash = app(TrashService::class);
        $count = $trash->emptyTrash();
        $this->trashCount = $trash->getTrashCount();

        $this->success(
            __(':count item berhasil dihapus permanen dari Tempat Sampah.', ['count' => $count]),
            __('Tempat Sampah')
        );

        $this->dispatch('trash-updated', count: $this->trashCount);
    }

    #[On('restore-all-trash')]
    public function restoreAllTrash(): void
    {
        /** @var TrashService $trash */
        $trash = app(TrashService::class);
        $count = $trash->restoreAll();
        $this->trashCount = $trash->getTrashCount();

        $this->success(
            __(':count berkas berhasil dipulihkan ke lokasi semula.', ['count' => $count]),
            __('Pulihkan Berkas')
        );

        $this->dispatch('trash-updated', count: $this->trashCount);
    }

    public function trashCount(): int
    {
        return $this->trashCount = app(TrashService::class)->getTrashCount();
    }

    public function getTrashCountProperty(): int
    {
        return $this->trashCount;
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
