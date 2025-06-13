<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Service;

use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Uid\Uuid;
use Xutim\CoreBundle\Domain\Model\FileInterface;
use Xutim\CoreBundle\Exception\CannotCreateDirectoryException;

readonly class FileUploader
{
    public function __construct(private string $filesDirectory)
    {
    }

    public function upload(UploadedFile $file, Uuid $id): string
    {
        $fileName = sprintf(
            '%s.%s',
            $id,
            $file->guessExtension() ?? 'unknown'
        );

        $targetPath = $this->getFilesPath();
        $this->createTargetDir($targetPath);
        $file->move($targetPath, $fileName);

        return $fileName;
    }

    public function getFilesPath(): string
    {
        return $this->filesDirectory;
    }

    public function deleteFile(string $name): void
    {
        $path = $this->getFilesPath() . $name;
        if (file_exists($path)) {
            unlink($path);
        }
    }

    public function getPathForFile(FileInterface $file): string
    {
        return $this->filesDirectory . $file->getFileName();
    }

    public function getFileSize(FileInterface $file): int
    {
        $size = filesize($this->getPathForFile($file));
        if ($size === false) {
            return -1;
        }

        return $size;
    }

    private function createTargetDir(string $path): void
    {
        if (!file_exists($path)) {
            if (!mkdir($path, 0740, true)) {
                throw new CannotCreateDirectoryException();
            }
        }
    }
}
