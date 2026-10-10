<?php

namespace Novay\MiniOS\Apps;

use Novay\MiniOS\Contracts\DesktopApp;
use Novay\MiniOS\Livewire\Apps\About;
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
        return [
            '/desktop/about'
        ];
    }

    public function version(): string
    {
        return '1.0.0';
    }

    public function isPinned(): bool
    {
        return false;
    }

    public function component(): ?string
    {
        return About::class;
    }

    public function window(): WindowConfig
    {
        return WindowConfig::make()
            ->size(480, 720)
            ->min(400, 560)
            ->resizable(false)
            ->maximizable(false)
            ->center(true);
    }
}
