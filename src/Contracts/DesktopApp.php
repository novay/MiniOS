<?php

namespace Novay\MiniOS\Contracts;

use Novay\MiniOS\Support\WindowConfig;

interface DesktopApp
{
    /**
     * Unique identifier of the desktop application.
     */
    public function id(): string;

    /**
     * Display name in topbar, window title, and dock.
     */
    public function name(): string;

    /**
     * Application icon name (Flux icon, SVG name, or asset URL).
     */
    public function icon(): string;

    /**
     * Default entry URL when launching the application.
     */
    public function entry(): string;

    /**
     * Array of URL paths owned by this application.
     *
     * @return array<int, string>
     */
    public function routes(): array;

    /**
     * Whether the application is pinned to the dock by default.
     */
    public function isPinned(): bool;

    /**
     * Livewire component class name or Blade view name.
     */
    public function component(): ?string;

    /**
     * Window configuration dimensions.
     */
    public function window(): WindowConfig;
}
