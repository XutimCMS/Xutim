<?php

declare(strict_types=1);


namespace Xutim\CoreBundle\File;

use Xutim\CoreBundle\Domain\Model\FileInterface;
use Xutim\CoreBundle\Infra\Image\FileInfo;
use Xutim\CoreBundle\Infra\Image\ImageInfo;
use Xutim\CoreBundle\Service\FileUploader;

final class FileInfoService
{
    public function __construct(private readonly FileUploader $fileUploader)
    {
    }

    public function getImageInfo(FileInterface $file): ?ImageInfo
    {
        $imagePath = $this->fileUploader->getPathForFile($file);

        if (!file_exists($imagePath) || !is_file($imagePath)) {
            return null;
        }

        $size = getimagesize($imagePath);
        if ($size === false) {
            return null;
        }
        /** @var int<0, max> $width */
        $width = $size[0];
        /** @var int<0, max> $height */
        $height = $size[1];

        return ImageInfo::fromDimensions($width, $height);
    }

    public function getFileInfo(FileInterface $file): ?FileInfo
    {
        $path = $this->fileUploader->getPathForFile($file);

        $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));
        $size = filesize($path);
        if ($size === false) {
            return null;
        }

        return new FileInfo($size, $extension);
    }
}
