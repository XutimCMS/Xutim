<?php

declare(strict_types=1);

namespace Xutim\MediaBundle\Infra\Spatie;

use Jcupitt\Vips\Image as VipsImage;
use Spatie\Image\Enums\Fit;
use Spatie\Image\Enums\ImageDriver;
use Spatie\Image\Image;
use Xutim\MediaBundle\Domain\Data\FitMode;
use Xutim\MediaBundle\Service\ImageProcessorInterface;

final class SpatieImageProcessor implements ImageProcessorInterface
{
    private const array SUPPORTED_FORMATS = ['webp', 'avif', 'jpg', 'jpeg', 'png', 'gif'];

    public function __construct(
        private readonly string $driver = 'imagick',
        private readonly bool $optimize = true,
    ) {
    }

    public function process(
        string $sourcePath,
        string $destPath,
        int $width,
        int $height,
        FitMode $fitMode,
        string $format,
        int $quality,
        ?float $focalX,
        ?float $focalY,
    ): array {
        $destDir = dirname($destPath);
        if (!is_dir($destDir) && !mkdir($destDir, 0775, true) && !is_dir($destDir)) {
            throw new \RuntimeException(sprintf('Failed to create directory: %s', $destDir));
        }

        $image = $this->createImage($sourcePath);
        $originalWidth = $image->getWidth();
        $originalHeight = $image->getHeight();

        $cropCenterX = $focalX !== null ? (int) round($focalX * $originalWidth) : null;
        $cropCenterY = $focalY !== null ? (int) round($focalY * $originalHeight) : null;

        match ($fitMode) {
            FitMode::Cover => $this->applyCover($image, $width, $height, $cropCenterX, $cropCenterY),
            FitMode::Contain => $image->fit(Fit::Contain, $width, $height),
            FitMode::Scale => $image->fit(Fit::Stretch, $width, $height),
        };

        $image->format($this->normalizeFormat($format));
        $image->quality($quality);

        $finalWidth = $image->getWidth();
        $finalHeight = $image->getHeight();

        if ($this->driver === 'vips') {
            /** @var VipsImage $vipsImage */
            $vipsImage = $image->image();
            $vipsImage->writeToFile($destPath, ['strip' => true, 'Q' => $quality]);
            unset($vipsImage, $image);
            gc_collect_cycles();
        } else {
            if ($this->optimize) {
                $image->optimize();
            }

            $image->save($destPath);
            unset($image);
        }

        $fileSize = filesize($destPath);

        return [
            'width' => $finalWidth,
            'height' => $finalHeight,
            'sizeBytes' => $fileSize !== false ? $fileSize : 0,
        ];
    }

    public function getDimensions(string $path): array
    {
        $image = $this->createImage($path);

        return [
            'width' => $image->getWidth(),
            'height' => $image->getHeight(),
        ];
    }

    public function supportsFormat(string $format): bool
    {
        return in_array(strtolower($format), self::SUPPORTED_FORMATS, true);
    }

    private function createImage(string $path): Image
    {
        $driver = match ($this->driver) {
            'imagick' => ImageDriver::Imagick,
            'gd' => ImageDriver::Gd,
            'vips' => ImageDriver::Vips,
            default => ImageDriver::Imagick,
        };

        return Image::useImageDriver($driver)->loadFile($path);
    }

    private function applyCover(Image $image, int $width, int $height, ?int $cropCenterX, ?int $cropCenterY): void
    {
        if ($cropCenterX === null || $cropCenterY === null) {
            $image->fit(Fit::Crop, $width, $height);

            return;
        }

        $originalWidth = $image->getWidth();
        $originalHeight = $image->getHeight();

        // Crop the largest region at the target aspect ratio, centered on the focal point.
        $targetAspect = $width / $height;
        $originalAspect = $originalWidth / $originalHeight;

        if ($originalAspect > $targetAspect) {
            $cropH = $originalHeight;
            $cropW = (int) round($originalHeight * $targetAspect);
        } else {
            $cropW = $originalWidth;
            $cropH = (int) round($originalWidth / $targetAspect);
        }

        $image->focalCrop($cropW, $cropH, $cropCenterX, $cropCenterY);
        $image->resize($width, $height);
    }

    private function normalizeFormat(string $format): string
    {
        return match (strtolower($format)) {
            'jpg' => 'jpeg',
            default => strtolower($format),
        };
    }
}
