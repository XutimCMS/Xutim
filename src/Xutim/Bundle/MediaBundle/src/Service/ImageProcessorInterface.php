<?php

declare(strict_types=1);

namespace Xutim\MediaBundle\Service;

use Xutim\MediaBundle\Domain\Data\FitMode;

interface ImageProcessorInterface
{
    /**
     * Process image and save to destination path
     *
     * @param string     $sourcePath Absolute path to source image
     * @param string     $destPath   Absolute path to save processed image
     * @param int        $width      Target width
     * @param int        $height     Target height
     * @param FitMode    $fitMode    How to fit the image
     * @param string     $format     Output format (webp, avif, jpg, png)
     * @param int        $quality    Output quality (0-100)
     * @param float|null $focalX     Focal point X (0-1), null for center
     * @param float|null $focalY     Focal point Y (0-1), null for center
     *
     * @return array{width: int, height: int, sizeBytes: int} Actual output dimensions and file size
     */
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
    ): array;

    /**
     * @return array{width: int, height: int}
     */
    public function getDimensions(string $path): array;

    public function supportsFormat(string $format): bool;
}
