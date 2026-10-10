<?php

namespace Novay\MiniOS\Apps;

use Novay\MiniOS\Contracts\DesktopApp;
use Novay\MiniOS\Livewire\Apps\Editor;
use Novay\MiniOS\Support\WindowConfig;

class EditorApp implements DesktopApp
{
    public function id(): string
    {
        return 'editor';
    }

    public function name(): string
    {
        return 'Editor';
    }

    public function icon(): string
    {
        return 'editor';
    }

    public function entry(): string
    {
        return '/editor';
    }

    public function routes(): array
    {
        return ['/editor'];
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
        return Editor::class;
    }

    public function window(): WindowConfig
    {
        return WindowConfig::make()
            ->size(800, 560)
            ->min(450, 320);
    }
}
