<?php

namespace Novay\MiniOS\Apps;

use Novay\MiniOS\Contracts\DesktopApp;
use Novay\MiniOS\Livewire\Apps\Terminal;
use Novay\MiniOS\Support\WindowConfig;

class TerminalApp implements DesktopApp
{
    public function id(): string
    {
        return 'terminal';
    }

    public function name(): string
    {
        return 'Terminal';
    }

    public function icon(): string
    {
        return 'terminal';
    }

    public function entry(): string
    {
        return '/terminal';
    }

    public function routes(): array
    {
        return ['/terminal'];
    }

    public function isPinned(): bool
    {
        return true;
    }

    public function component(): ?string
    {
        return Terminal::class;
    }

    public function window(): WindowConfig
    {
        return WindowConfig::make()
            ->size(780, 500)
            ->min(480, 300);
    }
}
