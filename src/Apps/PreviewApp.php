<?php

namespace Novay\MiniOS\Apps;

use Novay\MiniOS\Contracts\DesktopApp;
use Novay\MiniOS\Livewire\Apps\Preview;
use Novay\MiniOS\Support\WindowConfig;

class PreviewApp implements DesktopApp
{
    public function id(): string
    {
        return 'preview';
    }

    public function name(): string
    {
        return 'Preview';
    }

    public function icon(): string
    {
        return 'preview';
    }

    public function entry(): string
    {
        return '/preview';
    }

    public function routes(): array
    {
        return ['/preview'];
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
        return class_exists(Preview::class) ? Preview::class : 'apps.preview';
    }

    public function window(): WindowConfig
    {
        return WindowConfig::make()
            ->size(850, 600)
            ->min(450, 350);
    }
}
