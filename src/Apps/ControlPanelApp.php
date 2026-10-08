<?php

namespace Novay\MiniOS\Apps;

use Novay\MiniOS\Contracts\DesktopApp;
use Novay\MiniOS\Livewire\Apps\ControlPanel;
use Novay\MiniOS\Support\WindowConfig;

class ControlPanelApp implements DesktopApp
{
    public function id(): string
    {
        return 'control-panel';
    }

    public function name(): string
    {
        return 'Control Panel';
    }

    public function icon(): string
    {
        return 'control-panel';
    }

    public function entry(): string
    {
        return '/desktop/control-panel';
    }

    public function routes(): array
    {
        return [
            '/control-panel',
            '/desktop/control-panel',
        ];
    }

    public function isPinned(): bool
    {
        return true;
    }

    public function component(): ?string
    {
        return class_exists(ControlPanel::class) ? ControlPanel::class : 'apps.control-panel';
    }

    public function window(): WindowConfig
    {
        return WindowConfig::make()
            ->size(980, 640)
            ->min(640, 420);
    }
}
