<?php

namespace Novay\MiniOS\Services;

use Illuminate\Support\Facades\File;

class TrashService
{
    /**
     * Get absolute path to the trash directory.
     */
    public function getTrashPath(): string
    {
        return storage_path('.trash');
    }

    /**
     * Ensure trash directory exists.
     */
    public function ensureTrashDirectory(): void
    {
        $path = $this->getTrashPath();
        if (! File::exists($path)) {
            File::makeDirectory($path, 0755, true, true);
        }
    }

    /**
     * Get path to metadata storage file.
     */
    protected function getMetadataFilePath(): string
    {
        return $this->getTrashPath().'/.metadata.json';
    }

    /**
     * Read trash metadata mapping.
     *
     * @return array<string, array<string, mixed>>
     */
    public function getMetadata(): array
    {
        $file = $this->getMetadataFilePath();
        if (! File::exists($file)) {
            return [];
        }

        try {
            $content = File::get($file);
            $data = json_decode($content, true);

            return is_array($data) ? $data : [];
        } catch (\Throwable $e) {
            return [];
        }
    }

    /**
     * Save metadata mapping.
     *
     * @param  array<string, array<string, mixed>>  $data
     */
    public function saveMetadata(array $data): void
    {
        $this->ensureTrashDirectory();
        $file = $this->getMetadataFilePath();
        File::put($file, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
    }

    /**
     * Soft delete a file or directory by moving it to .trash.
     *
     * @param  string  $relativePath  Relative to storage_path()
     * @return array<string, mixed>
     */
    public function moveToTrash(string $relativePath): array
    {
        $this->ensureTrashDirectory();

        $relativePath = trim(str_replace('\\', '/', $relativePath), '/');
        $sourcePath = storage_path($relativePath);

        if (! File::exists($sourcePath)) {
            throw new \RuntimeException("File or directory not found: {$relativePath}");
        }

        $isDir = File::isDirectory($sourcePath);
        $originalName = basename($sourcePath);
        $uniqueTrashName = time().'_'.uniqid().'_'.$originalName;
        $destinationPath = $this->getTrashPath().'/'.$uniqueTrashName;

        // Move physical file or directory
        File::move($sourcePath, $destinationPath);

        // Store metadata
        $metadata = $this->getMetadata();
        $metadata[$uniqueTrashName] = [
            'trash_name' => $uniqueTrashName,
            'original_name' => $originalName,
            'original_path' => $relativePath,
            'is_dir' => $isDir,
            'deleted_at' => now()->toDateTimeString(),
        ];
        $this->saveMetadata($metadata);

        return [
            'trash_name' => $uniqueTrashName,
            'original_name' => $originalName,
            'original_path' => $relativePath,
            'is_dir' => $isDir,
        ];
    }

    /**
     * Restore an item from trash back to its original location.
     */
    public function restore(string $trashName): bool
    {
        $metadata = $this->getMetadata();
        if (! isset($metadata[$trashName])) {
            return false;
        }

        $itemMeta = $metadata[$trashName];
        $trashPath = $this->getTrashPath().'/'.$trashName;

        if (! File::exists($trashPath)) {
            unset($metadata[$trashName]);
            $this->saveMetadata($metadata);

            return false;
        }

        $originalRelative = $itemMeta['original_path'];
        $originalPath = storage_path($originalRelative);

        // Ensure parent directory exists
        $parentDir = dirname($originalPath);
        if (! File::exists($parentDir)) {
            File::makeDirectory($parentDir, 0755, true, true);
        }

        // Avoid overwrite conflict if file already exists at original location
        if (File::exists($originalPath)) {
            $pathInfo = pathinfo($originalPath);
            $dirname = $pathInfo['dirname'];
            $filename = $pathInfo['filename'];
            $extension = isset($pathInfo['extension']) ? '.'.$pathInfo['extension'] : '';
            $originalPath = $dirname.'/'.$filename.'_restored_'.time().$extension;
        }

        File::move($trashPath, $originalPath);

        unset($metadata[$trashName]);
        $this->saveMetadata($metadata);

        return true;
    }

    /**
     * Restore all items in trash.
     *
     * @return int Number of items restored
     */
    public function restoreAll(): int
    {
        $metadata = $this->getMetadata();
        $count = 0;

        foreach (array_keys($metadata) as $trashName) {
            if ($this->restore($trashName)) {
                $count++;
            }
        }

        return $count;
    }

    /**
     * Permanently delete a single item from trash.
     */
    public function deletePermanently(string $trashName): bool
    {
        $trashPath = $this->getTrashPath().'/'.$trashName;
        $deleted = false;

        if (File::exists($trashPath)) {
            if (File::isDirectory($trashPath)) {
                $deleted = File::deleteDirectory($trashPath);
            } else {
                $deleted = File::delete($trashPath);
            }
        }

        $metadata = $this->getMetadata();
        if (isset($metadata[$trashName])) {
            unset($metadata[$trashName]);
            $this->saveMetadata($metadata);
        }

        return $deleted;
    }

    /**
     * Empty entire trash can.
     *
     * @return int Number of items cleared
     */
    public function emptyTrash(): int
    {
        $path = $this->getTrashPath();
        if (! File::exists($path)) {
            return 0;
        }

        $count = 0;
        $files = File::files($path);
        $directories = File::directories($path);

        foreach ($files as $file) {
            if ($file->getFilename() === '.metadata.json') {
                continue;
            }
            File::delete($file->getPathname());
            $count++;
        }

        foreach ($directories as $dir) {
            File::deleteDirectory($dir);
            $count++;
        }

        // Reset metadata
        $this->saveMetadata([]);

        return $count;
    }

    /**
     * Get count of items currently in trash.
     */
    public function getTrashCount(): int
    {
        $metadata = $this->getMetadata();

        return count($metadata);
    }
}
