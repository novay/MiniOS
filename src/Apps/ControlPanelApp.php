<?php

namespace Novay\MiniOS\Apps;

/**
 * @deprecated Use KatalogApp instead.
 */
class ControlPanelApp extends KatalogApp
{
    public function id(): string
    {
        return 'control-panel';
    }

    public function name(): string
    {
        return 'Katalog';
    }

    public function icon(): string
    {
        return 'katalog';
    }

    public function entry(): string
    {
        return '/desktop/control-panel';
    }

    public function routes(): array
    {
        return [
            '/control-panel',
            '/desktop/control-panel',
        ];
    }
}
