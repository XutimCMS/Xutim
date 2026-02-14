<?php

declare(strict_types=1);

namespace Xutim\MediaBundle\Service;

use Xutim\MediaBundle\Domain\Data\GeneratedVariant;
use Xutim\MediaBundle\Domain\Data\ImagePreset;
use Xutim\MediaBundle\Domain\Model\MediaInterface;
use Xutim\MediaBundle\Infra\Storage\StorageAdapterInterface;

final class VariantGenerator
{
    public function __construct(
        private readonly ImageProcessorInterface $processor,
        private readonly VariantPathResolver $pathResolver,
        private readonly StorageAdapterInterface $storage,
        private readonly PresetRegistry $presetRegistry,
    ) {
    }

    /**
     * @return list<GeneratedVariant>
     */
    public function generateAllPresets(MediaInterface $media): array
    {
        if (!$media->isImage()) {
            return [];
        }

        $variants = [];
        foreach ($this->presetRegistry->all() as $preset) {
            $variants = [...$variants, ...$this->generateForPreset($media, $preset)];
        }

        return $variants;
    }

    /**
     * @return list<GeneratedVariant>
     */
    public function generateForPreset(MediaInterface $media, ImagePreset $preset): array
    {
        if (!$media->isImage()) {
            return [];
        }

        $sourcePath = $this->storage->absolutePath($media->originalPath());
        $variants = [];

        foreach ($preset->getEffectiveWidths() as $width) {
            foreach ($preset->formats as $format) {
                if (!$this->processor->supportsFormat($format)) {
                    continue;
                }

                $variant = $this->generateVariant($media, $preset, $width, $format, $sourcePath);
                $variants[] = $variant;
            }
        }

        return $variants;
    }

    public function generateVariant(
        MediaInterface $media,
        ImagePreset $preset,
        int $width,
        string $format,
        ?string $sourcePath = null,
    ): GeneratedVariant {
        $sourcePath ??= $this->storage->absolutePath($media->originalPath());
        $destPath = $this->pathResolver->getAbsolutePath($media, $preset, $width, $format);
        $height = $preset->calculateHeight($width);

        $focalX = $preset->useFocalPoint ? $media->focalX() : null;
        $focalY = $preset->useFocalPoint ? $media->focalY() : null;

        $result = $this->processor->process(
            $sourcePath,
            $destPath,
            $width,
            $height,
            $preset->fitMode,
            $format,
            $preset->qualityFor($format),
            $focalX,
            $focalY,
        );

        $path = $this->pathResolver->getPath($media, $preset, $width, $format);
        $fingerprint = $this->pathResolver->calculateFingerprint($media, $preset, $width, $format);

        return new GeneratedVariant(
            preset: $preset->name,
            format: $format,
            width: $result['width'],
            height: $result['height'],
            path: $path,
            fingerprint: $fingerprint,
            sizeBytes: $result['sizeBytes'],
        );
    }
}
