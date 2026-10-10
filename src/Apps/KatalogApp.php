<?php

namespace Novay\MiniOS\Apps;

use Novay\MiniOS\Contracts\DesktopApp;
use Novay\MiniOS\Livewire\Apps\Katalog;
use Novay\MiniOS\Support\WindowConfig;

class KatalogApp implements DesktopApp
{
    public function id(): string
    {
        return 'katalog';
    }

    public function name(): string
    {
        return 'Katalog';
    }

    public function icon(): string
    {
        return 'katalog';
    }

    public function entry(): string
    {
        return '/desktop/katalog';
    }

    public function routes(): array
    {
        return [
            '/katalog',
            '/desktop/katalog',
            '/control-panel',
            '/desktop/control-panel',
        ];
    }

    public function isPinned(): bool
    {
        return true;
    }

    public function version(): string
    {
        return '1.0.0';
    }

    public function component(): ?string
    {
        return class_exists(Katalog::class) ? Katalog::class : 'apps.katalog';
    }

    public function window(): WindowConfig
    {
        return WindowConfig::make()
            ->size(980, 640)
            ->min(640, 420);
    }
}
