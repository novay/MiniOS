<?php

namespace Novay\MiniOS\Apps;

use Novay\MiniOS\Contracts\DesktopApp;
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

    public function isPinned(): bool
    {
        return true;
    }

    public function component(): ?string
    {
        return 'minios.calculator-window';
    }

    public function window(): WindowConfig
    {
        return WindowConfig::make()
            ->size(320, 480)
            ->min(300, 440);
    }
}
