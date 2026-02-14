<?php

declare(strict_types=1);

namespace Xutim\MediaBundle\Infra\Storage;

use RuntimeException;

final class StorageException extends RuntimeException
{
    public static function fileNotFound(string $path): self
    {
        return new self(sprintf('File not found: %s', $path));
    }

    public static function writeFailed(string $path, string $reason): self
    {
        return new self(sprintf('Failed to write file %s: %s', $path, $reason));
    }

    public static function deleteFailed(string $path, string $reason): self
    {
        return new self(sprintf('Failed to delete file %s: %s', $path, $reason));
    }

    public static function notSupported(string $operation): self
    {
        return new self(sprintf('Operation not supported: %s', $operation));
    }
}
