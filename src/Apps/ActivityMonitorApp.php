<?php

namespace Novay\MiniOS\Apps;

use Novay\MiniOS\Contracts\DesktopApp;
use Novay\MiniOS\Livewire\Apps\ActivityMonitor;
use Novay\MiniOS\Support\WindowConfig;

class ActivityMonitorApp implements DesktopApp
{
    public function id(): string
    {
        return 'activity-monitor';
    }

    public function name(): string
    {
        return 'Activity Monitor';
    }

    public function icon(): string
    {
        return 'activity-monitor';
    }

    public function entry(): string
    {
        return '/activity-monitor';
    }

    public function routes(): array
    {
        return ['/activity-monitor'];
    }

    public function isPinned(): bool
    {
        return true;
    }

    public function component(): ?string
    {
        return class_exists(ActivityMonitor::class) ? ActivityMonitor::class : 'apps.activity-monitor';
    }

    public function window(): WindowConfig
    {
        return WindowConfig::make()
            ->size(850, 550)
            ->min(600, 400);
    }
}
