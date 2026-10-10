<?php

namespace Novay\MiniOS\Apps;

use Novay\MiniOS\Contracts\DesktopApp;
use Novay\MiniOS\Livewire\Apps\Player;
use Novay\MiniOS\Support\WindowConfig;

class PlayerApp implements DesktopApp
{
    public function id(): string
    {
        return 'player';
    }

    public function name(): string
    {
        return 'Player';
    }

    public function icon(): string
    {
        return 'player';
    }

    public function entry(): string
    {
        return '/player';
    }

    public function routes(): array
    {
        return ['/player'];
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
        return Player::class;
    }

    public function window(): WindowConfig
    {
        return WindowConfig::make()
            ->size(760, 520)
            ->min(440, 340);
    }
}
