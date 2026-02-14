<?php

declare(strict_types=1);

namespace Xutim\MediaBundle\Service;

use Xutim\MediaBundle\Infra\Storage\StorageAdapterInterface;

final class BlurHashGenerator
{
    private const int BLUR_COMPONENTS_X = 4;
    private const int BLUR_COMPONENTS_Y = 3;

    public function __construct(
        private readonly StorageAdapterInterface $storage,
    ) {
    }

    /**
     * Generate BlurHash from image file
     *
     * Requires kornrunner/blurhash package to be installed.
     * Returns null if package is not available.
     */
    public function generate(string $originalPath): ?string
    {
        if (!class_exists(\kornrunner\Blurhash\Blurhash::class)) {
            return null;
        }

        $absolutePath = $this->storage->absolutePath($originalPath);

        if (!file_exists($absolutePath)) {
            return null;
        }

        $contents = file_get_contents($absolutePath);
        if ($contents === false) {
            return null;
        }

        $image = imagecreatefromstring($contents);
        if ($image === false) {
            return null;
        }

        $width = imagesx($image);
        $height = imagesy($image);

        $pixels = [];
        for ($y = 0; $y < $height; $y++) {
            $row = [];
            for ($x = 0; $x < $width; $x++) {
                $rgb = imagecolorat($image, $x, $y);
                if ($rgb === false) {
                    continue;
                }
                $row[] = [
                    ($rgb >> 16) & 0xFF,
                    ($rgb >> 8) & 0xFF,
                    $rgb & 0xFF,
                ];
            }
            $pixels[] = $row;
        }

        unset($image);

        /** @var string */
        return \kornrunner\Blurhash\Blurhash::encode(
            $pixels,
            self::BLUR_COMPONENTS_X,
            self::BLUR_COMPONENTS_Y,
        );
    }
}
