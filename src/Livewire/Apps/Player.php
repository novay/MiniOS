<?php

namespace Novay\MiniOS\Livewire\Apps;

use Illuminate\Support\Facades\File;
use Livewire\Attributes\On;
use Livewire\Component;
use Novay\MiniOS\Concerns\HasNotifications;
use Novay\MiniOS\Concerns\HasTranslations;

class Player extends Component
{
    use HasNotifications;
    use HasTranslations;

    public ?string $filePath = null;

    public ?string $fileName = null;

    public ?string $fileSize = null;

    public ?string $extension = null;

    public string $mediaType = 'empty'; // 'audio', 'video', 'empty'

    public ?string $mediaData = null;

    public string $mimeType = '';

    public bool $showInfo = false;

    public function mount(?string $path = null): void
    {
        if ($path) {
            $this->loadMedia($path);
        }
    }

    #[On('open-file')]
    public function onOpenFile(mixed $app = null, ?string $path = null, ?string $name = null, ?string $id = null, mixed $payload = null): void
    {
        $targetApp = null;
        $targetPath = null;

        if (is_array($app)) {
            $targetApp = $app['app'] ?? $app['id'] ?? null;
            $targetPath = $app['path'] ?? null;
        } elseif (is_string($app)) {
            $targetApp = $app;
            $targetPath = $path;
        }

        if (is_array($payload)) {
            $targetApp = $targetApp ?: ($payload['app'] ?? $payload['id'] ?? null);
            $targetPath = $targetPath ?: ($payload['path'] ?? null);
        } elseif (is_string($payload)) {
            $targetPath = $targetPath ?: $payload;
        }

        if (! $targetApp && $id) {
            $targetApp = $id;
        }

        if ($targetApp === 'player' && is_string($targetPath) && ! empty($targetPath)) {
            $this->loadMedia($targetPath);
        }
    }

    #[On('open-player')]
    public function onOpenPlayer(?string $path = null, mixed $payload = null): void
    {
        $targetPath = $path;
        if (is_array($payload)) {
            $targetPath = $targetPath ?: ($payload['path'] ?? null);
        } elseif (is_string($payload)) {
            $targetPath = $targetPath ?: $payload;
        }

        if (is_string($targetPath) && ! empty($targetPath)) {
            $this->loadMedia($targetPath);
        }
    }

    public function loadMedia(string $path): void
    {
        $sanitized = ltrim(str_replace(['../', '..\\'], '', $path), '/');
        $fullPath = storage_path($sanitized);
        $realPath = realpath($fullPath) ?: $fullPath;

        if (! File::exists($realPath) || File::isDirectory($realPath)) {
            $this->notify($this->trans('error_file_not_found'), 'error');

            return;
        }

        $extension = strtolower(pathinfo($realPath, PATHINFO_EXTENSION));
        $basename = basename($realPath);
        $sizeBytes = File::size($realPath);

        $audioExts = ['mp3', 'wav', 'ogg', 'm4a', 'flac', 'aac'];
        $videoExts = ['mp4', 'webm', 'mov', 'mkv', 'avi'];

        if (in_array($extension, $audioExts, true)) {
            $this->mediaType = 'audio';
            $this->mimeType = $this->getAudioMime($extension);
        } elseif (in_array($extension, $videoExts, true)) {
            $this->mediaType = 'video';
            $this->mimeType = $this->getVideoMime($extension);
        } else {
            $this->mediaType = 'empty';
            $this->mediaData = null;
            $this->notify($this->trans('error_file_not_found'), 'error');

            return;
        }

        // Limit direct in-memory base64 streaming to 40 MB
        if ($sizeBytes > 40 * 1024 * 1024) {
            $this->notify($this->trans('error_too_large'), 'warning');
            $this->mediaData = null;
        } else {
            $this->mediaData = 'data:'.$this->mimeType.';base64,'.base64_encode(File::get($realPath));
        }

        $this->filePath = $sanitized;
        $this->fileName = $basename;
        $this->extension = $extension;
        $this->fileSize = $this->formatBytes($sizeBytes);
        $this->showInfo = false;

        $this->dispatch('player-media-loaded', [
            'type' => $this->mediaType,
            'name' => $this->fileName,
            'src' => $this->mediaData,
        ]);
    }

    public function closeMedia(): void
    {
        $this->filePath = null;
        $this->fileName = null;
        $this->fileSize = null;
        $this->extension = null;
        $this->mediaType = 'empty';
        $this->mediaData = null;
        $this->mimeType = '';
        $this->showInfo = false;

        $this->dispatch('player-media-closed');
    }

    public function toggleInfo(): void
    {
        $this->showInfo = ! $this->showInfo;
    }

    public function downloadMedia()
    {
        if (! $this->filePath) {
            return null;
        }

        $fullPath = storage_path($this->filePath);
        if (! File::exists($fullPath) || File::isDirectory($fullPath)) {
            $this->notify($this->trans('error_file_not_found'), 'error');

            return null;
        }

        return response()->download($fullPath, $this->fileName);
    }

    protected function getAudioMime(string $ext): string
    {
        return match ($ext) {
            'mp3' => 'audio/mpeg',
            'wav' => 'audio/wav',
            'ogg' => 'audio/ogg',
            'm4a' => 'audio/mp4',
            'flac' => 'audio/flac',
            'aac' => 'audio/aac',
            default => 'audio/mpeg',
        };
    }

    protected function getVideoMime(string $ext): string
    {
        return match ($ext) {
            'mp4' => 'video/mp4',
            'webm' => 'video/webm',
            'mov' => 'video/quicktime',
            'mkv' => 'video/x-matroska',
            'avi' => 'video/x-msvideo',
            default => 'video/mp4',
        };
    }

    protected function formatBytes(int $bytes): string
    {
        if ($bytes < 1024) {
            return $bytes.' B';
        }
        if ($bytes < 1048576) {
            return round($bytes / 1024, 1).' KB';
        }
        if ($bytes < 1073741824) {
            return round($bytes / 1048576, 1).' MB';
        }

        return round($bytes / 1073741824, 2).' GB';
    }

    public function render()
    {
        return view('minios::apps.player');
    }
}
