<?php

declare(strict_types=1);

namespace Xutim\MediaBundle\Service;

use Xutim\MediaBundle\Domain\Data\ImagePreset;
use Xutim\MediaBundle\Domain\Model\MediaInterface;
use Xutim\MediaBundle\Domain\Model\MediaVariantInterface;
use Xutim\MediaBundle\Infra\Storage\StorageAdapterInterface;

final class VariantPathResolver
{
    public function __construct(
        private readonly StorageAdapterInterface $storage,
    ) {
    }

    /**
     * Get public URL for an existing variant
     */
    public function getUrl(MediaVariantInterface $variant): string
    {
        return $this->storage->url($variant->path()) . '?v=' . substr($variant->fingerprint(), 0, 8);
    }

    /**
     * Get absolute filesystem path for a variant to be generated
     */
    public function getAbsolutePath(MediaInterface $media, ImagePreset $preset, int $width, string $format): string
    {
        $path = $this->buildPath($media, $preset, $width, $format);

        return $this->storage->absolutePath($path);
    }

    /**
     * Build storage-relative path for a variant (used during generation)
     */
    public function buildPath(MediaInterface $media, ImagePreset $preset, int $width, string $format): string
    {
        $hash = $this->getMediaHash($media);

        return sprintf(
            'variants/%s/%d/%s/%s.%s',
            $preset->name,
            $width,
            $format,
            $hash,
            $format,
        );
    }

    /**
     * Get fingerprint for cache busting
     */
    public function calculateFingerprint(MediaInterface $media, ImagePreset $preset, int $width, string $format): string
    {
        $recipe = sprintf(
            '%s:%d:%d:%s:%s:%d:%s',
            $preset->name,
            $preset->maxWidth,
            $preset->maxHeight,
            $preset->fitMode->value,
            $format,
            $preset->qualityFor($format),
            $width,
        );

        return hash('sha256', $media->hash() . ':' . $recipe);
    }

    private function getMediaHash(MediaInterface $media): string
    {
        // Use first 16 chars of media hash for shorter paths
        return substr($media->hash(), 0, 16);
    }
}
