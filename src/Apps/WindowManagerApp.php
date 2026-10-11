<?php

namespace Novay\MiniOS\Apps;

use Novay\MiniOS\Contracts\DesktopApp;
use Novay\MiniOS\Livewire\Apps\WindowManager;
use Novay\MiniOS\Support\WindowConfig;

class WindowManagerApp implements DesktopApp
{
    public function id(): string
    {
        return 'window-manager';
    }

    public function name(): string
    {
        return 'Window Manager';
    }

    public function icon(): string
    {
        return 'squares-2x2';
    }

    public function entry(): string
    {
        return '/window-manager';
    }

    public function routes(): array
    {
        return ['/window-manager'];
    }

    public function isPinned(): bool
    {
        return false;
    }

    public function component(): ?string
    {
        return WindowManager::class;
    }

    public function window(): WindowConfig
    {
        return WindowConfig::make()->size(1100, 730)->min(800, 550);
    }
}
