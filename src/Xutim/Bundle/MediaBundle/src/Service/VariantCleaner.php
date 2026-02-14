<?php

declare(strict_types=1);

namespace Xutim\MediaBundle\Service;

use Xutim\MediaBundle\Domain\Data\ImagePreset;
use Xutim\MediaBundle\Domain\Model\MediaInterface;
use Xutim\MediaBundle\Infra\Storage\StorageAdapterInterface;

final class VariantCleaner
{
    public function __construct(
        private readonly StorageAdapterInterface $storage,
        private readonly VariantPathResolver $pathResolver,
        private readonly PresetRegistry $presetRegistry,
    ) {
    }

    public function cleanForMedia(MediaInterface $media): int
    {
        $deleted = 0;

        foreach ($this->presetRegistry->all() as $preset) {
            $deleted += $this->cleanForPreset($media, $preset);
        }

        return $deleted;
    }

    public function cleanForPreset(MediaInterface $media, ImagePreset $preset): int
    {
        $deleted = 0;

        foreach ($preset->getEffectiveWidths() as $width) {
            foreach ($preset->formats as $format) {
                $path = $this->pathResolver->getPath($media, $preset, $width, $format);

                if ($this->storage->exists($path)) {
                    $this->storage->delete($path);
                    $deleted++;
                }
            }
        }

        return $deleted;
    }
}
