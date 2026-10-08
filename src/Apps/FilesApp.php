<?php

namespace Novay\MiniOS\Apps;

use Novay\MiniOS\Contracts\DesktopApp;
use Novay\MiniOS\Livewire\Apps\Files;
use Novay\MiniOS\Support\WindowConfig;

class FilesApp implements DesktopApp
{
    public function id(): string
    {
        return 'files';
    }

    public function name(): string
    {
        return 'Files';
    }

    public function icon(): string
    {
        return 'files';
    }

    public function entry(): string
    {
        return '/files';
    }

    public function routes(): array
    {
        return ['/files'];
    }

    public function isPinned(): bool
    {
        return true;
    }

    public function component(): ?string
    {
        return class_exists(Files::class) ? Files::class : 'apps.files';
    }

    public function window(): WindowConfig
    {
        return WindowConfig::make()
            ->size(900, 600)
            ->min(500, 350);
    }
}
