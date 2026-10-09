<?php

namespace Novay\MiniOS\Apps;

use Novay\MiniOS\Contracts\DesktopApp;
use Novay\MiniOS\Livewire\Apps\Soundcloud;
use Novay\MiniOS\Support\WindowConfig;

class SoundcloudApp implements DesktopApp
{
    public function id(): string
    {
        return 'soundcloud';
    }

    public function name(): string
    {
        return 'SoundCloud';
    }

    public function icon(): string
    {
        return 'soundcloud';
    }

    public function entry(): string
    {
        return '/soundcloud';
    }

    public function routes(): array
    {
        return ['/soundcloud'];
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
        return class_exists(Soundcloud::class) ? Soundcloud::class : 'apps.soundcloud';
    }

    public function window(): WindowConfig
    {
        return WindowConfig::make()
            ->size(520, 640)
            ->min(380, 320)
            ->resizable(false)
            ->maximizable(false);
    }
}
