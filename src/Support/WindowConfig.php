<?php

namespace Novay\MiniOS\Support;

class WindowConfig
{
    protected int $width = 900;

    protected int $height = 600;

    protected int $minWidth = 500;

    protected int $minHeight = 350;

    protected ?int $maxWidth = null;

    protected ?int $maxHeight = null;

    protected bool $center = false;

    protected bool $resizable = true;

    protected bool $maximizable = true;

    public static function make(): self
    {
        return new self;
    }

    public function size(int $width, int $height): self
    {
        $this->width = $width;
        $this->height = $height;

        return $this;
    }

    public function min(int $width, int $height): self
    {
        $this->minWidth = $width;
        $this->minHeight = $height;

        return $this;
    }

    public function max(int $width, int $height): self
    {
        $this->maxWidth = $width;
        $this->maxHeight = $height;

        return $this;
    }

    public function center(bool $center = true): self
    {
        $this->center = $center;

        return $this;
    }

    public function resizable(bool $resizable = true): self
    {
        $this->resizable = $resizable;

        return $this;
    }

    public function maximizable(bool $maximizable = true): self
    {
        $this->maximizable = $maximizable;

        return $this;
    }

    public function isResizable(): bool
    {
        return $this->resizable;
    }

    public function isMaximizable(): bool
    {
        return $this->maximizable;
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return array_filter([
            'width' => $this->width,
            'height' => $this->height,
            'min_width' => $this->minWidth,
            'min_height' => $this->minHeight,
            'max_width' => $this->maxWidth,
            'max_height' => $this->maxHeight,
            'center' => $this->center,
            'resizable' => $this->resizable,
            'maximizable' => $this->maximizable,
        ], fn ($val) => $val !== null);
    }
}
