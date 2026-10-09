<?php

namespace Novay\MiniOS\Livewire\Apps;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithFileUploads;
use Novay\MiniOS\Concerns\HasNotifications;
use Novay\MiniOS\Concerns\HasTranslations;
use Novay\MiniOS\Services\TrashService;

class Files extends Component
{
    use HasNotifications {
        notify as osNotify;
    }
    use HasTranslations;
    use WithFileUploads;

    public string $currentPath = '';

    public string $viewMode = 'grid';

    // Cloud Storage Modal & Info State
    public bool $showCloudStorageModal = false;

    public ?string $cloudStorageTestStatus = null;

    public ?string $cloudStorageTestError = null;

    public string $searchQuery = '';

    public array $history = [''];

    public int $historyIndex = 0;

    public string $sortBy = 'name';

    public string $sortDirection = 'asc';

    public ?string $selectedPath = null;

    public ?string $statusMessage = null;

    public string $statusType = 'success';

    // New Folder Modal State
    public bool $showNewFolderModal = false;

    public string $newFolderName = '';

    // New File Modal State
    public bool $showNewFileModal = false;

    public string $newFileName = '';

    // Upload Files Modal State
    public bool $showUploadModal = false;

    public array $uploadedFiles = [];

    // Rename Modal State
    public bool $showRenameModal = false;

    public string $renameTargetPath = '';

    public string $renameTargetName = '';

    public string $renameNewName = '';

    // Delete Confirmation Modal State
    public bool $showDeleteModal = false;

    public string $deleteTargetPath = '';

    public string $deleteTargetName = '';

    public bool $deleteIsDirectory = false;

    // Preview / Text Editor State
    public ?array $previewItem = null;

    public ?string $previewContent = null;

    public int $trashCount = 0;

    public bool $showDetailsPanel = true;

    public function mount(string $path = ''): void
    {
        $this->currentPath = $this->sanitizePath($path);
        $this->history = [$this->currentPath];
        $this->historyIndex = 0;
        $this->trashCount = app(TrashService::class)->getTrashCount();
    }

    public function navigate(string $path): void
    {
        $sanitized = $this->sanitizePath($path);
        if ($sanitized !== $this->currentPath) {
            $this->currentPath = $sanitized;
            $this->history = array_slice($this->history, 0, $this->historyIndex + 1);
            $this->history[] = $this->currentPath;
            $this->historyIndex = count($this->history) - 1;
        }
        $this->searchQuery = '';
        $this->selectedPath = null;
    }

    public function toggleDetailsPanel(): void
    {
        $this->showDetailsPanel = ! $this->showDetailsPanel;
    }

    public function navigateUp(): void
    {
        if (empty($this->currentPath)) {
            return;
        }

        $parts = explode('/', str_replace('\\', '/', $this->currentPath));
        array_pop($parts);
        $newPath = implode('/', array_filter($parts));
        $this->navigate($newPath);
    }

    public function goBack(): void
    {
        if ($this->historyIndex > 0) {
            $this->historyIndex--;
            $this->currentPath = $this->history[$this->historyIndex];
            $this->searchQuery = '';
        }
    }

    public function goForward(): void
    {
        if ($this->historyIndex < count($this->history) - 1) {
            $this->historyIndex++;
            $this->currentPath = $this->history[$this->historyIndex];
            $this->searchQuery = '';
        }
    }

    public function getCanGoBackProperty(): bool
    {
        return $this->historyIndex > 0;
    }

    public function getCanGoForwardProperty(): bool
    {
        return $this->historyIndex < count($this->history) - 1;
    }

    public function sort(string $column): void
    {
        if ($this->sortBy === $column) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortBy = $column;
            $this->sortDirection = 'asc';
        }
    }

    public function setViewMode(string $mode): void
    {
        if (in_array($mode, ['grid', 'list'])) {
            $this->viewMode = $mode;
        }
    }

    #[On('open-folder')]
    public function openFolder(string $path): void
    {
        $this->navigate($path);
    }

    #[On('trash-updated')]
    public function onTrashUpdated(mixed $count = null): void
    {
        if (is_array($count)) {
            $count = $count['count'] ?? null;
        }

        $this->trashCount = is_numeric($count) ? (int) $count : app(TrashService::class)->getTrashCount();
    }

    protected function sanitizePath(string $path): string
    {
        $path = trim(str_replace('\\', '/', $path), '/');

        if (str_starts_with($path, 'files/')) {
            $path = substr($path, 6);
        } elseif ($path === 'files') {
            $path = '';
        }

        if ($path === 'trash' || str_starts_with($path, 'trash/')) {
            $path = '.trash'.substr($path, 5);
        }

        $parts = array_filter(explode('/', $path), function ($part) {
            return $part !== '' && $part !== '.' && $part !== '..';
        });

        return implode('/', $parts);
    }

    public function getAbsolutePathProperty(): string
    {
        $base = storage_path();
        if (empty($this->currentPath)) {
            return $base;
        }

        $fullPath = $base.'/'.$this->currentPath;
        $realPath = realpath($fullPath) ?: $fullPath;
        if (! str_starts_with($realPath, $base)) {
            return $base;
        }

        return $fullPath;
    }

    public function getBreadcrumbsProperty(): array
    {
        $breadcrumbs = [
            ['name' => 'storage', 'path' => ''],
        ];

        if (empty($this->currentPath)) {
            return $breadcrumbs;
        }

        $accumulated = '';
        foreach (explode('/', $this->currentPath) as $part) {
            if ($part === '') {
                continue;
            }
            $accumulated = $accumulated === '' ? $part : $accumulated.'/'.$part;
            $breadcrumbs[] = [
                'name' => $part,
                'path' => $accumulated,
            ];
        }

        return $breadcrumbs;
    }

    public function getIsLockedProperty(): bool
    {
        return ! auth()->check();
    }

    public function getIsInTrashProperty(): bool
    {
        return $this->currentPath === '.trash' || str_starts_with($this->currentPath, '.trash/');
    }

    public function getIsSystemProtectedProperty(): bool
    {
        return $this->currentPath === 'logs'
            || str_starts_with($this->currentPath, 'logs/')
            || $this->currentPath === 'framework'
            || str_starts_with($this->currentPath, 'framework/');
    }

    public function trashCount(): int
    {
        return $this->trashCount = app(TrashService::class)->getTrashCount();
    }

    public function getTrashCountProperty(): int
    {
        return $this->trashCount;
    }

    public function getSelectedItemProperty(): ?array
    {
        if (! $this->selectedPath) {
            return null;
        }

        foreach ($this->items as $item) {
            if ($item['path'] === $this->selectedPath) {
                $item['preview_thumb'] = null;
                if (! $item['is_dir'] && in_array($item['extension'], ['png', 'jpg', 'jpeg', 'gif', 'webp', 'svg'])) {
                    $realPath = storage_path($this->sanitizePath($item['path']));
                    if (File::exists($realPath) && File::size($realPath) <= 2 * 1024 * 1024) {
                        $mime = $item['extension'] === 'svg' ? 'image/svg+xml' : 'image/'.$item['extension'];
                        $item['preview_thumb'] = 'data:'.$mime.';base64,'.base64_encode(File::get($realPath));
                    }
                }

                return $item;
            }
        }

        return null;
    }

    public function getItemsProperty(): array
    {
        if ($this->isLocked) {
            return [];
        }

        $absPath = $this->absolutePath;

        if (! File::exists($absPath) || ! File::isDirectory($absPath)) {
            return [];
        }

        $dirs = [];
        $files = [];

        if ($this->searchQuery !== '') {
            $query = strtolower(trim($this->searchQuery));
            $basePath = storage_path();

            try {
                // Recursive search in all subdirectories
                $allDirs = File::allDirectories($basePath);
                foreach ($allDirs as $dir) {
                    $basename = basename($dir);
                    if (str_contains(strtolower($basename), $query)) {
                        $rel = ltrim(substr($dir, strlen($basePath)), '/\\');
                        $relPath = str_replace('\\', '/', $rel);
                        $childCount = count(File::directories($dir)) + count(File::files($dir));
                        $dirs[] = [
                            'name' => $basename,
                            'path' => $relPath,
                            'location' => dirname($relPath) === '.' ? 'storage' : dirname($relPath),
                            'is_dir' => true,
                            'size' => "{$childCount} item".($childCount !== 1 ? 's' : ''),
                            'bytes' => 0,
                            'extension' => 'folder',
                            'updated_at' => date('d M Y, H:i', File::lastModified($dir)),
                            'raw_mtime' => File::lastModified($dir),
                        ];
                    }
                    if (count($dirs) >= 100) {
                        break;
                    }
                }

                // Recursive search in all files
                $allFiles = File::allFiles($basePath);
                foreach ($allFiles as $file) {
                    $basename = $file->getFilename();
                    if (str_contains(strtolower($basename), $query)) {
                        $rel = ltrim(substr($file->getPathname(), strlen($basePath)), '/\\');
                        $relPath = str_replace('\\', '/', $rel);
                        $extension = strtolower($file->getExtension());
                        $sizeBytes = $file->getSize();
                        $files[] = [
                            'name' => $basename,
                            'path' => $relPath,
                            'location' => dirname($relPath) === '.' ? 'storage' : dirname($relPath),
                            'is_dir' => false,
                            'size' => $this->formatBytes($sizeBytes),
                            'bytes' => $sizeBytes,
                            'extension' => $extension ?: 'file',
                            'updated_at' => date('d M Y, H:i', $file->getMTime()),
                            'raw_mtime' => $file->getMTime(),
                        ];
                    }
                    if (count($files) >= 150) {
                        break;
                    }
                }
            } catch (\Exception $e) {
                return [];
            }
        } elseif ($this->isInTrash) {
            try {
                /** @var TrashService $trashService */
                $trashService = app(TrashService::class);
                $metadata = $trashService->getMetadata();

                foreach ($metadata as $trashName => $meta) {
                    $itemPath = storage_path('.trash/'.$trashName);
                    if (! File::exists($itemPath)) {
                        continue;
                    }

                    $isDir = (bool) ($meta['is_dir'] ?? File::isDirectory($itemPath));
                    $sizeBytes = $isDir ? 0 : File::size($itemPath);
                    $originalName = $meta['original_name'] ?? $trashName;
                    $extension = $isDir ? 'folder' : strtolower(pathinfo($originalName, PATHINFO_EXTENSION));

                    $itemData = [
                        'name' => $originalName,
                        'trash_name' => $trashName,
                        'path' => '.trash/'.$trashName,
                        'original_path' => $meta['original_path'] ?? '',
                        'location' => dirname($meta['original_path'] ?? '') === '.' ? 'storage' : dirname($meta['original_path'] ?? ''),
                        'is_dir' => $isDir,
                        'size' => $isDir ? 'Folder' : $this->formatBytes($sizeBytes),
                        'bytes' => $sizeBytes,
                        'extension' => $extension ?: 'file',
                        'updated_at' => isset($meta['deleted_at']) ? date('d M Y, H:i', strtotime($meta['deleted_at'])) : date('d M Y, H:i', File::lastModified($itemPath)),
                        'raw_mtime' => isset($meta['deleted_at']) ? strtotime($meta['deleted_at']) : File::lastModified($itemPath),
                        'is_trash_item' => true,
                    ];

                    if ($isDir) {
                        $dirs[] = $itemData;
                    } else {
                        $files[] = $itemData;
                    }
                }
            } catch (\Exception $e) {
                return [];
            }
        } else {
            try {
                $contents = File::directories($absPath);
                foreach ($contents as $dir) {
                    $basename = basename($dir);
                    if (empty($this->currentPath) && $basename === '.trash') {
                        continue;
                    }
                    $relativePath = empty($this->currentPath) ? $basename : $this->currentPath.'/'.$basename;
                    $childCount = count(File::directories($dir)) + count(File::files($dir));
                    $dirs[] = [
                        'name' => $basename,
                        'path' => $relativePath,
                        'location' => $this->currentPath ?: 'storage',
                        'is_dir' => true,
                        'size' => "{$childCount} item".($childCount !== 1 ? 's' : ''),
                        'bytes' => 0,
                        'extension' => 'folder',
                        'updated_at' => date('d M Y, H:i', File::lastModified($dir)),
                        'raw_mtime' => File::lastModified($dir),
                    ];
                }

                $fileContents = File::files($absPath);
                foreach ($fileContents as $file) {
                    $basename = $file->getFilename();
                    if (empty($this->currentPath) && ($basename === '.trash' || $basename === '.metadata.json')) {
                        continue;
                    }
                    $relativePath = empty($this->currentPath) ? $basename : $this->currentPath.'/'.$basename;
                    $extension = strtolower($file->getExtension());
                    $sizeBytes = $file->getSize();
                    $files[] = [
                        'name' => $basename,
                        'path' => $relativePath,
                        'location' => $this->currentPath ?: 'storage',
                        'is_dir' => false,
                        'size' => $this->formatBytes($sizeBytes),
                        'bytes' => $sizeBytes,
                        'extension' => $extension ?: 'file',
                        'updated_at' => date('d M Y, H:i', $file->getMTime()),
                        'raw_mtime' => $file->getMTime(),
                    ];
                }
            } catch (\Exception $e) {
                return [];
            }
        }

        $sorter = function ($a, $b) {
            $valA = match ($this->sortBy) {
                'size' => $a['bytes'] ?? 0,
                'updated_at' => $a['raw_mtime'] ?? 0,
                default => strtolower($a['name']),
            };
            $valB = match ($this->sortBy) {
                'size' => $b['bytes'] ?? 0,
                'updated_at' => $b['raw_mtime'] ?? 0,
                default => strtolower($b['name']),
            };

            if ($valA == $valB) {
                return 0;
            }

            $res = ($valA < $valB) ? -1 : 1;

            return $this->sortDirection === 'desc' ? -$res : $res;
        };

        usort($dirs, $sorter);
        usort($files, $sorter);

        return array_merge($dirs, $files);
    }

    public function selectItem(?string $path): void
    {
        $this->selectedPath = $this->selectedPath === $path ? null : $path;
    }

    public function notify(string $message, string $type = 'success'): void
    {
        $this->statusMessage = $message;
        $this->statusType = $type;

        $variant = match ($type) {
            'error' => 'danger',
            'warning' => 'warning',
            default => 'success',
        };

        $this->osNotify($message, $this->trans('app_title'), $variant);
    }

    public function clearNotification(): void
    {
        $this->statusMessage = null;
    }

    public function openNewFolderModal(): void
    {
        $this->newFolderName = '';
        $this->showNewFolderModal = true;
    }

    public function closeNewFolderModal(): void
    {
        $this->showNewFolderModal = false;
        $this->newFolderName = '';
    }

    public function createFolder(): void
    {
        if ($this->isLocked) {
            return;
        }

        $name = trim($this->newFolderName);
        if ($name === '' || str_contains($name, '/') || str_contains($name, '\\') || $name === '.' || $name === '..') {
            $this->notify($this->trans('error_folder_name_invalid'), 'error');

            return;
        }

        $targetDir = $this->absolutePath.'/'.$name;
        if (File::exists($targetDir)) {
            $this->notify($this->trans('error_folder_exists'), 'error');

            return;
        }

        try {
            File::makeDirectory($targetDir, 0755, true);
            $this->notify($this->trans('success_folder_created', ['name' => $name]));
            $this->closeNewFolderModal();
        } catch (\Exception $e) {
            $this->notify($this->trans('error_folder_failed', ['error' => $e->getMessage()]), 'error');
        }
    }

    public function openNewFileModal(): void
    {
        $this->newFileName = '';
        $this->showNewFileModal = true;
    }

    public function closeNewFileModal(): void
    {
        $this->showNewFileModal = false;
        $this->newFileName = '';
    }

    public function createFile(): void
    {
        if ($this->isLocked) {
            return;
        }

        $name = trim($this->newFileName);
        if ($name === '' || str_contains($name, '/') || str_contains($name, '\\') || $name === '.' || $name === '..') {
            $this->notify($this->trans('error_file_name_invalid'), 'error');

            return;
        }

        $targetFile = $this->absolutePath.'/'.$name;
        if (File::exists($targetFile)) {
            $this->notify($this->trans('error_file_exists'), 'error');

            return;
        }

        try {
            File::put($targetFile, '');
            $this->notify($this->trans('success_file_created', ['name' => $name]));
            $this->closeNewFileModal();
        } catch (\Exception $e) {
            $this->notify($this->trans('error_file_failed', ['error' => $e->getMessage()]), 'error');
        }
    }

    public function openUploadModal(): void
    {
        $this->uploadedFiles = [];
        $this->showUploadModal = true;
    }

    public function closeUploadModal(): void
    {
        $this->showUploadModal = false;
        $this->uploadedFiles = [];
    }

    public function uploadFiles(): void
    {
        if ($this->isLocked) {
            return;
        }

        if (empty($this->uploadedFiles)) {
            $this->notify($this->trans('error_upload_no_files'), 'error');

            return;
        }

        $targetDir = $this->absolutePath;
        if (! File::isDirectory($targetDir)) {
            File::makeDirectory($targetDir, 0755, true);
        }

        $uploadedCount = 0;
        foreach ($this->uploadedFiles as $file) {
            $originalName = $file->getClientOriginalName();
            $safeName = basename(str_replace(['/', '\\'], '_', $originalName));
            $destination = $targetDir.'/'.$safeName;

            try {
                File::put($destination, File::get($file->getRealPath()));
                $uploadedCount++;
            } catch (\Exception $e) {
                // Continue with next
            }
        }

        $this->notify($this->trans('success_files_uploaded', ['count' => $uploadedCount]));
        $this->closeUploadModal();
    }

    public function openRenameModal(string $path): void
    {
        $this->renameTargetPath = $this->sanitizePath($path);
        $this->renameTargetName = basename($this->renameTargetPath);
        $this->renameNewName = $this->renameTargetName;
        $this->showRenameModal = true;
    }

    public function closeRenameModal(): void
    {
        $this->showRenameModal = false;
        $this->renameTargetPath = '';
        $this->renameTargetName = '';
        $this->renameNewName = '';
    }

    public function rename(): void
    {
        if ($this->isLocked) {
            return;
        }

        $newName = trim($this->renameNewName);
        if ($newName === '' || str_contains($newName, '/') || str_contains($newName, '\\') || $newName === '.' || $newName === '..') {
            $this->notify($this->trans('error_rename_invalid'), 'error');

            return;
        }

        $oldFullPath = storage_path($this->renameTargetPath);
        if (! File::exists($oldFullPath)) {
            $this->notify($this->trans('error_rename_not_found'), 'error');

            return;
        }

        $parentDir = dirname($oldFullPath);
        $newFullPath = $parentDir.'/'.$newName;

        if (File::exists($newFullPath) && $newFullPath !== $oldFullPath) {
            $this->notify($this->trans('error_rename_exists'), 'error');

            return;
        }

        try {
            File::move($oldFullPath, $newFullPath);
            $this->notify($this->trans('success_renamed', ['name' => $newName]));
            $this->closeRenameModal();
        } catch (\Exception $e) {
            $this->notify($this->trans('error_rename_failed', ['error' => $e->getMessage()]), 'error');
        }
    }

    public function openDeleteModal(string $path, bool $isDir = false): void
    {
        $this->deleteTargetPath = $this->sanitizePath($path);
        $this->deleteTargetName = basename($this->deleteTargetPath);
        $this->deleteIsDirectory = $isDir;
        $this->showDeleteModal = true;
    }

    public function closeDeleteModal(): void
    {
        $this->showDeleteModal = false;
        $this->deleteTargetPath = '';
        $this->deleteTargetName = '';
        $this->deleteIsDirectory = false;
    }

    public function delete(): void
    {
        if ($this->isLocked) {
            return;
        }

        $fullPath = storage_path($this->deleteTargetPath);
        if (! File::exists($fullPath)) {
            $this->notify($this->trans('error_delete_not_found'), 'error');
            $this->closeDeleteModal();

            return;
        }

        try {
            /** @var TrashService $trashService */
            $trashService = app(TrashService::class);

            if ($this->isInTrash) {
                // Permanently delete item
                $trashName = basename($fullPath);
                $trashService->deletePermanently($trashName);
                $this->notify($this->trans('success_deleted_permanently', ['name' => $this->deleteTargetName]));
            } else {
                // Soft delete to trash
                $trashService->moveToTrash($this->deleteTargetPath);
                $this->notify($this->trans('success_moved_to_trash', ['name' => $this->deleteTargetName]));
            }

            $this->closeDeleteModal();
            if ($this->selectedPath === $this->deleteTargetPath) {
                $this->selectedPath = null;
            }
            $this->trashCount = $trashService->getTrashCount();
            $this->dispatch('trash-updated', count: $this->trashCount);
        } catch (\Exception $e) {
            $this->notify($this->trans('error_delete_failed', ['error' => $e->getMessage()]), 'error');
        }
    }

    public function restorePath(string $path): void
    {
        $trashName = basename($path);
        /** @var TrashService $trashService */
        $trashService = app(TrashService::class);

        if ($trashService->restore($trashName)) {
            $this->notify($this->trans('success_restored'));
            if ($this->selectedPath === $path) {
                $this->selectedPath = null;
            }
            $this->trashCount = $trashService->getTrashCount();
            $this->dispatch('trash-updated', count: $this->trashCount);
        } else {
            $this->notify($this->trans('error_restore_failed'), 'error');
        }
    }

    public function restoreSelected(): void
    {
        if (! $this->selectedPath) {
            return;
        }

        $this->restorePath($this->selectedPath);
    }

    public function restoreAllTrash(): void
    {
        /** @var TrashService $trashService */
        $trashService = app(TrashService::class);
        $count = $trashService->restoreAll();

        $this->notify($this->trans('success_restored_all', ['count' => $count]));
        $this->selectedPath = null;
        $this->trashCount = $trashService->getTrashCount();
        $this->dispatch('trash-updated', count: $this->trashCount);
    }

    public function emptyTrash(): void
    {
        /** @var TrashService $trashService */
        $trashService = app(TrashService::class);
        $count = $trashService->emptyTrash();

        $this->notify($this->trans('success_trash_emptied', ['count' => $count]));
        $this->selectedPath = null;
        $this->trashCount = $trashService->getTrashCount();
        $this->dispatch('trash-updated', count: $this->trashCount);
    }

    public function downloadFile(string $path)
    {
        if ($this->isLocked) {
            return null;
        }

        $sanitized = $this->sanitizePath($path);
        $fullPath = storage_path($sanitized);

        if (! File::exists($fullPath) || File::isDirectory($fullPath)) {
            $this->notify($this->trans('error_download_not_found'), 'error');

            return null;
        }

        return response()->download($fullPath);
    }

    public function saveTextFile(): void
    {
        if ($this->isLocked || ! $this->previewItem) {
            return;
        }

        $fullPath = storage_path($this->previewItem['path']);
        if (! File::exists($fullPath) || File::isDirectory($fullPath)) {
            $this->notify($this->trans('error_save_not_found'), 'error');

            return;
        }

        try {
            File::put($fullPath, $this->previewContent ?? '');
            $sizeBytes = File::size($fullPath);
            $this->previewItem['size'] = $this->formatBytes($sizeBytes);
            $this->previewItem['data'] = $this->previewContent;
            $this->notify($this->trans('success_file_saved'));
        } catch (\Exception $e) {
            $this->notify($this->trans('error_save_failed', ['error' => $e->getMessage()]), 'error');
        }
    }

    public function openFile(string $path, ?string $forceApp = null): void
    {
        if ($this->isLocked) {
            return;
        }

        $sanitized = $this->sanitizePath($path);
        $fullPath = storage_path($sanitized);
        $realPath = realpath($fullPath) ?: $fullPath;

        if (! File::exists($realPath) || File::isDirectory($realPath)) {
            return;
        }

        $appId = $forceApp ?: $this->getAssociatedApp($sanitized);
        $basename = basename($fullPath);

        // Dispatch events for desktop window manager and target application
        $this->dispatch('open-app', id: $appId, app: $appId, path: $sanitized, name: $basename);
        $this->dispatch('open-file', id: $appId, app: $appId, path: $sanitized, name: $basename);

        // Populate previewItem for backwards compatibility if needed
        $extension = strtolower(pathinfo($basename, PATHINFO_EXTENSION));
        $sizeBytes = File::size($realPath);
        $this->previewContent = in_array($appId, ['textedit']) && $sizeBytes < 100000 ? File::get($realPath) : null;
        $this->previewItem = [
            'name' => $basename,
            'path' => $sanitized,
            'extension' => $extension,
            'size' => $this->formatBytes($sizeBytes),
            'updated_at' => date('d M Y, H:i', File::lastModified($realPath)),
            'type' => $appId,
            'data' => $this->previewContent,
        ];
    }

    public function getAssociatedApp(string $path): string
    {
        $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));

        $imageAndPdf = ['png', 'jpg', 'jpeg', 'gif', 'webp', 'svg', 'bmp', 'ico', 'pdf'];
        $mediaExts = ['mp3', 'wav', 'ogg', 'm4a', 'flac', 'aac', 'mp4', 'webm', 'mov', 'mkv', 'avi'];

        if (in_array($extension, $imageAndPdf, true)) {
            return 'preview';
        }

        if (in_array($extension, $mediaExts, true)) {
            return 'player';
        }

        return 'textedit';
    }

    public function openPreview(string $path): void
    {
        $this->openFile($path);
    }

    public function closePreview(): void
    {
        $this->previewItem = null;
        $this->previewContent = null;
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

        return round($bytes / 1073741824, 1).' GB';
    }

    /**
     * Get active Cloud Storage details from MiniOS settings.
     *
     * @return array{driver: string, name: string, target: string, region: string, endpoint: string, is_active: bool}|null
     */
    public function getCloudStorageInfoProperty(): ?array
    {
        $driver = os_setting('services.storage_driver', 'local');

        if ($driver === 's3') {
            $bucket = os_setting('services.s3_bucket', '');

            return [
                'driver' => 's3',
                'name' => 'S3 Cloud Storage',
                'target' => $bucket ?: 'Default Bucket',
                'region' => os_setting('services.s3_region', 'us-east-1'),
                'endpoint' => os_setting('services.s3_endpoint', ''),
                'is_active' => true,
            ];
        }

        if ($driver === 'bunny') {
            $zone = os_setting('services.bunny_storage_zone', '');

            return [
                'driver' => 'bunny',
                'name' => 'BunnyCDN Storage',
                'target' => $zone ?: 'Default Zone',
                'region' => os_setting('services.bunny_region', 'de'),
                'endpoint' => os_setting('services.bunny_pull_zone', ''),
                'is_active' => true,
            ];
        }

        // Check if user has configured S3 or Bunny even if default driver is currently local
        $s3Bucket = os_setting('services.s3_bucket');
        if (! empty($s3Bucket)) {
            return [
                'driver' => 's3',
                'name' => 'S3 Cloud Storage',
                'target' => $s3Bucket,
                'region' => os_setting('services.s3_region', 'us-east-1'),
                'endpoint' => os_setting('services.s3_endpoint', ''),
                'is_active' => false,
            ];
        }

        $bunnyZone = os_setting('services.bunny_storage_zone');
        if (! empty($bunnyZone)) {
            return [
                'driver' => 'bunny',
                'name' => 'BunnyCDN Storage',
                'target' => $bunnyZone,
                'region' => os_setting('services.bunny_region', 'de'),
                'endpoint' => os_setting('services.bunny_pull_zone', ''),
                'is_active' => false,
            ];
        }

        return null;
    }

    public function openCloudStorageModal(): void
    {
        $this->cloudStorageTestStatus = null;
        $this->cloudStorageTestError = null;
        $this->showCloudStorageModal = true;
    }

    public function closeCloudStorageModal(): void
    {
        $this->showCloudStorageModal = false;
        $this->cloudStorageTestStatus = null;
        $this->cloudStorageTestError = null;
    }

    public function testCloudStorageConnection(): void
    {
        $this->cloudStorageTestStatus = null;
        $this->cloudStorageTestError = null;

        $info = $this->cloudStorageInfo;
        if (! $info) {
            $this->cloudStorageTestError = $this->trans('error_cloud_not_configured');

            return;
        }

        try {
            $driver = $info['driver'];
            $disk = Storage::disk($driver);
            $testFileName = 'minios_cloud_probe_'.time().'.txt';
            $testContent = 'MiniOS Cloud Probe from Files App at '.now()->toIso8601String();

            $disk->put($testFileName, $testContent);
            $disk->delete($testFileName);

            $this->cloudStorageTestStatus = $this->trans('success_cloud_connected', ['name' => $info['name'], 'target' => $info['target']]);
        } catch (\Throwable $e) {
            $this->cloudStorageTestError = $this->trans('error_cloud_failed', ['error' => $e->getMessage()]);
        }
    }

    /**
     * Get dynamic accent color configuration based on appearance settings.
     *
     * @return array<string, string>
     */
    public function getAccentProperty(): array
    {
        $color = os_setting()->get('appearance.accent_color', 'indigo');

        return match ($color) {
            'zinc' => [
                'name' => 'zinc',
                'bg' => 'bg-zinc-700',
                'bg_hover' => 'hover:bg-zinc-800',
                'badge' => 'bg-zinc-800 text-white shadow-zinc-500/30',
                'active_tab' => 'bg-zinc-700 text-white shadow-sm font-medium',
                'radio_card' => 'border-zinc-500 bg-zinc-500/10 ring-1 ring-zinc-500',
                'selected_row' => 'bg-zinc-500/10 dark:bg-zinc-500/15 ring-1 ring-inset ring-zinc-500/30 font-medium',
                'ring' => 'focus:ring-zinc-500 focus:border-zinc-500',
                'text' => 'text-zinc-600 dark:text-zinc-400',
                'hex' => '#71717a',
            ],
            'emerald' => [
                'name' => 'emerald',
                'bg' => 'bg-emerald-600',
                'bg_hover' => 'hover:bg-emerald-700',
                'badge' => 'bg-emerald-600 text-white shadow-emerald-500/30',
                'active_tab' => 'bg-emerald-600 text-white shadow-sm font-medium',
                'radio_card' => 'border-emerald-500 bg-emerald-500/10 ring-1 ring-emerald-500',
                'selected_row' => 'bg-emerald-500/10 dark:bg-emerald-500/15 ring-1 ring-inset ring-emerald-500/30 font-medium',
                'ring' => 'focus:ring-emerald-500 focus:border-emerald-500',
                'text' => 'text-emerald-600 dark:text-emerald-400',
                'hex' => '#10b981',
            ],
            'sky' => [
                'name' => 'sky',
                'bg' => 'bg-sky-500',
                'bg_hover' => 'hover:bg-sky-600',
                'badge' => 'bg-sky-500 text-white shadow-sky-500/30',
                'active_tab' => 'bg-sky-600 text-white shadow-sm font-medium',
                'radio_card' => 'border-sky-500 bg-sky-500/10 ring-1 ring-sky-500',
                'selected_row' => 'bg-sky-500/10 dark:bg-sky-500/15 ring-1 ring-inset ring-sky-500/30 font-medium',
                'ring' => 'focus:ring-sky-500 focus:border-sky-500',
                'text' => 'text-sky-600 dark:text-sky-400',
                'hex' => '#0ea5e9',
            ],
            'amber' => [
                'name' => 'amber',
                'bg' => 'bg-amber-500',
                'bg_hover' => 'hover:bg-amber-600',
                'badge' => 'bg-amber-500 text-white shadow-amber-500/30',
                'active_tab' => 'bg-amber-600 text-white shadow-sm font-medium',
                'radio_card' => 'border-amber-500 bg-amber-500/10 ring-1 ring-amber-500',
                'selected_row' => 'bg-amber-500/10 dark:bg-amber-500/15 ring-1 ring-inset ring-amber-500/30 font-medium',
                'ring' => 'focus:ring-amber-500 focus:border-amber-500',
                'text' => 'text-amber-600 dark:text-amber-400',
                'hex' => '#f59e0b',
            ],
            'rose' => [
                'name' => 'rose',
                'bg' => 'bg-rose-500',
                'bg_hover' => 'hover:bg-rose-600',
                'badge' => 'bg-rose-500 text-white shadow-rose-500/30',
                'active_tab' => 'bg-rose-600 text-white shadow-sm font-medium',
                'radio_card' => 'border-rose-500 bg-rose-500/10 ring-1 ring-rose-500',
                'selected_row' => 'bg-rose-500/10 dark:bg-rose-500/15 ring-1 ring-inset ring-rose-500/30 font-medium',
                'ring' => 'focus:ring-rose-500 focus:border-rose-500',
                'text' => 'text-rose-600 dark:text-rose-400',
                'hex' => '#f43f5e',
            ],
            'violet' => [
                'name' => 'violet',
                'bg' => 'bg-violet-600',
                'bg_hover' => 'hover:bg-violet-700',
                'badge' => 'bg-violet-600 text-white shadow-violet-500/30',
                'active_tab' => 'bg-violet-600 text-white shadow-sm font-medium',
                'radio_card' => 'border-violet-500 bg-violet-500/10 ring-1 ring-violet-500',
                'selected_row' => 'bg-violet-500/10 dark:bg-violet-500/15 ring-1 ring-inset ring-violet-500/30 font-medium',
                'ring' => 'focus:ring-violet-500 focus:border-violet-500',
                'text' => 'text-violet-600 dark:text-violet-400',
                'hex' => '#8b5cf6',
            ],
            default => [ // indigo
                'name' => 'indigo',
                'bg' => 'bg-indigo-600',
                'bg_hover' => 'hover:bg-indigo-700',
                'badge' => 'bg-indigo-600 text-white shadow-indigo-500/30',
                'active_tab' => 'bg-indigo-600 text-white shadow-sm font-medium',
                'radio_card' => 'border-indigo-500 bg-indigo-500/10 ring-1 ring-indigo-500',
                'selected_row' => 'bg-indigo-500/10 dark:bg-indigo-500/15 ring-1 ring-inset ring-indigo-500/30 font-medium',
                'ring' => 'focus:ring-indigo-500 focus:border-indigo-500',
                'text' => 'text-indigo-600 dark:text-indigo-400',
                'hex' => '#4f46e5',
            ],
        };
    }

    public function render()
    {
        $view = view()->exists('pages.minios.apps.files')
            ? 'pages.minios.apps.files'
            : 'minios::apps.files';

        return view($view, [
            'accent' => $this->accent,
        ]);
    }
}
