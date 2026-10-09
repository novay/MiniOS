<?php

namespace Novay\MiniOS\Apps;

use Novay\MiniOS\Contracts\DesktopApp;
use Novay\MiniOS\Livewire\Apps\Browser;
use Novay\MiniOS\Support\WindowConfig;

class BrowserApp implements DesktopApp
{
    public function id(): string
    {
        return 'browser';
    }

    public function name(): string
    {
        return 'Browser';
    }

    public function icon(): string
    {
        return 'browser';
    }

    public function entry(): string
    {
        return '/browser';
    }

    public function routes(): array
    {
        return ['/browser'];
    }

    public function version(): string
    {
        return '1.0.0';
    }

    public function isPinned(): bool
    {
        return true;
    }

    public function component(): ?string
    {
        return class_exists(Browser::class) ? Browser::class : 'apps.browser';
    }

    public function window(): WindowConfig
    {
        return WindowConfig::make()
            ->size(1100, 700)
            ->min(600, 400);
    }
}
