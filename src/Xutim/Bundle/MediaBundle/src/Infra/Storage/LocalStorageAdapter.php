<?php

declare(strict_types=1);

namespace Xutim\MediaBundle\Infra\Storage;

final class LocalStorageAdapter implements StorageAdapterInterface
{
    public function __construct(
        private readonly string $publicDir,
        private readonly string $mediaPath,
        private readonly string $urlPrefix,
    ) {
    }

    public function write(string $path, string $contents): void
    {
        $fullPath = $this->absolutePath($path);
        $dir = dirname($fullPath);

        if (!is_dir($dir) && !mkdir($dir, 0755, true) && !is_dir($dir)) {
            throw StorageException::writeFailed($path, 'Failed to create directory');
        }

        if (file_put_contents($fullPath, $contents) === false) {
            throw StorageException::writeFailed($path, 'Failed to write contents');
        }
    }

    public function read(string $path): string
    {
        $fullPath = $this->absolutePath($path);

        if (!file_exists($fullPath)) {
            throw StorageException::fileNotFound($path);
        }

        $contents = file_get_contents($fullPath);
        if ($contents === false) {
            throw StorageException::fileNotFound($path);
        }

        return $contents;
    }

    public function delete(string $path): void
    {
        $fullPath = $this->absolutePath($path);

        if (file_exists($fullPath) && !unlink($fullPath)) {
            throw StorageException::deleteFailed($path, 'Failed to delete file');
        }
    }

    public function exists(string $path): bool
    {
        return file_exists($this->absolutePath($path));
    }

    public function url(string $path): string
    {
        return rtrim($this->urlPrefix, '/') . '/' . ltrim($path, '/');
    }

    public function absolutePath(string $path): string
    {
        return $this->publicDir . '/' . $this->mediaPath . '/' . ltrim($path, '/');
    }
}
