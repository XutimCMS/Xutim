<?php

declare(strict_types=1);

namespace Xutim\MediaBundle\Infra\Storage;

interface StorageAdapterInterface
{
    /**
     * Write content to a path
     */
    public function write(string $path, string $contents): void;

    /**
     * Read content from a path
     *
     * @throws StorageException if file does not exist
     */
    public function read(string $path): string;

    /**
     * Delete a file at path
     */
    public function delete(string $path): void;

    /**
     * Check if a file exists at path
     */
    public function exists(string $path): bool;

    /**
     * Get public URL for a path
     */
    public function url(string $path): string;

    /**
     * Get absolute filesystem path (for local storage only)
     *
     * @throws StorageException if storage doesn't support filesystem paths
     */
    public function absolutePath(string $path): string;
}
