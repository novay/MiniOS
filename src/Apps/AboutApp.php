<?php

namespace Novay\MiniOS\Apps;

use Novay\MiniOS\Contracts\DesktopApp;
use Novay\MiniOS\Support\WindowConfig;

class AboutApp implements DesktopApp
{
    public function id(): string
    {
        return 'about';
    }

    public function name(): string
    {
        return 'Tentang MiniOS';
    }

    public function icon(): string
    {
        return 'about';
    }

    public function entry(): string
    {
        return '/desktop/about';
    }

    public function routes(): array
    {
        return ['/desktop/about'];
    }

    public function isPinned(): bool
    {
        return false;
    }

    public function component(): ?string
    {
        return 'minios.about-window';
    }

    public function window(): WindowConfig
    {
        return WindowConfig::make()
            ->size(460, 620)
            ->min(380, 620)
            ->center(true);
    }
}
