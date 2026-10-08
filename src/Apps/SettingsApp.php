<?php

namespace Novay\MiniOS\Apps;

use Novay\MiniOS\Contracts\DesktopApp;
use Novay\MiniOS\Livewire\Apps\Settings;
use Novay\MiniOS\Support\WindowConfig;

class SettingsApp implements DesktopApp
{
    public function id(): string
    {
        return 'settings';
    }

    public function name(): string
    {
        return 'Settings';
    }

    public function icon(): string
    {
        return 'settings';
    }

    public function entry(): string
    {
        return '/desktop/settings';
    }

    public function routes(): array
    {
        return ['/desktop/settings'];
    }

    public function isPinned(): bool
    {
        return true;
    }

    public function component(): ?string
    {
        return class_exists(Settings::class) ? Settings::class : 'apps.settings';
    }

    public function window(): WindowConfig
    {
        return WindowConfig::make()
            ->size(950, 650)
            ->min(600, 400);
    }
}
