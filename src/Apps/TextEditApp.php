<?php

namespace Novay\MiniOS\Apps;

use Novay\MiniOS\Contracts\DesktopApp;
use Novay\MiniOS\Livewire\Apps\TextEdit;
use Novay\MiniOS\Support\WindowConfig;

class TextEditApp implements DesktopApp
{
    public function id(): string
    {
        return 'textedit';
    }

    public function name(): string
    {
        return 'TextEdit';
    }

    public function icon(): string
    {
        return 'textedit';
    }

    public function entry(): string
    {
        return '/textedit';
    }

    public function routes(): array
    {
        return ['/textedit'];
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
        return class_exists(TextEdit::class) ? TextEdit::class : 'apps.textedit';
    }

    public function window(): WindowConfig
    {
        return WindowConfig::make()
            ->size(800, 560)
            ->min(450, 320);
    }
}
