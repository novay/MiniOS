<?php

namespace Novay\MiniOS\Apps;

use Novay\MiniOS\Contracts\DesktopApp;
use Novay\MiniOS\Livewire\Apps\Calculator;
use Novay\MiniOS\Support\WindowConfig;

class CalculatorApp implements DesktopApp
{
    public function id(): string
    {
        return 'calculator';
    }

    public function name(): string
    {
        return 'Calculator';
    }

    public function icon(): string
    {
        return 'calculator';
    }

    public function entry(): string
    {
        return '/calculator';
    }

    public function routes(): array
    {
        return ['/calculator'];
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
        return class_exists(Calculator::class) ? Calculator::class : 'apps.calculator';
    }

    public function window(): WindowConfig
    {
        return WindowConfig::make()
            ->size(320, 480)
            ->min(320, 480)
            ->resizable(false)
            ->maximizable(false);
    }
}
