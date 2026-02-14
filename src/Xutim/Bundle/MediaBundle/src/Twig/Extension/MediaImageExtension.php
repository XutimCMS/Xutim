<?php

declare(strict_types=1);

namespace Xutim\MediaBundle\Twig\Extension;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;
use Xutim\MediaBundle\Domain\Model\MediaInterface;
use Xutim\MediaBundle\Infra\Storage\StorageAdapterInterface;
use Xutim\MediaBundle\Repository\MediaRepositoryInterface;
use Xutim\MediaBundle\Repository\MediaVariantRepositoryInterface;
use Xutim\MediaBundle\Service\PresetRegistry;
use Xutim\MediaBundle\Service\VariantPathResolver;

final class MediaImageExtension extends AbstractExtension
{
    /** @var array<string, MediaInterface|false> */
    private array $resolvedMedia = [];

    public function __construct(
        private readonly PresetRegistry $presetRegistry,
        private readonly VariantPathResolver $pathResolver,
        private readonly MediaVariantRepositoryInterface $variantRepository,
        private readonly MediaRepositoryInterface $mediaRepository,
        private readonly StorageAdapterInterface $storage,
    ) {
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('media_url', $this->mediaUrl(...)),
            new TwigFunction('media_srcset', $this->mediaSrcset(...)),
        ];
    }

    /**
     * @param MediaInterface|string $media MediaInterface or file name (e.g. "uuid.ext")
     */
    public function mediaUrl(
        MediaInterface|string $media,
        string $presetName,
        ?int $width = null,
        string $format = 'webp',
    ): string {
        $originalFileName = is_string($media) ? $media : null;
        $media = $this->resolveMedia($media);

        if ($media === null) {
            return $originalFileName !== null
                ? $this->storage->url('uploads/' . $originalFileName)
                : '';
        }

        $preset = $this->presetRegistry->get($presetName);
        if ($preset === null) {
            return $this->storage->url($media->originalPath());
        }

        $effectiveWidths = $preset->getEffectiveWidths();
        if ($width === null) {
            $lastWidth = end($effectiveWidths);
            $width = $lastWidth !== false ? $lastWidth : $preset->maxWidth;
        }

        $variant = $this->variantRepository->findByMediaPresetFormatWidth(
            $media,
            $presetName,
            $format,
            $width,
        );

        if ($variant !== null) {
            return $this->pathResolver->getUrl($media, $preset, $width, $format, $variant->fingerprint());
        }

        // No variant — fall back to original file
        return $this->storage->url($media->originalPath());
    }

    /**
     * @param MediaInterface|string $media MediaInterface or file name (e.g. "uuid.ext")
     */
    public function mediaSrcset(
        MediaInterface|string $media,
        string $presetName,
        string $format = 'webp',
    ): string {
        $media = $this->resolveMedia($media);
        if ($media === null) {
            return '';
        }

        $preset = $this->presetRegistry->get($presetName);
        if ($preset === null) {
            return '';
        }

        $variants = $this->variantRepository->findByMediaPresetFormat($media, $presetName, $format);

        if ($variants === []) {
            return '';
        }

        $srcset = [];
        foreach ($variants as $variant) {
            $url = $this->pathResolver->getUrl(
                $media,
                $preset,
                $variant->width(),
                $format,
                $variant->fingerprint(),
            );
            $srcset[] = sprintf('%s %dw', $url, $variant->width());
        }

        return implode(', ', $srcset);
    }

    /**
     * Resolves a fileName string to MediaInterface, with caching.
     * Returns null if the media entity is not found.
     */
    private function resolveMedia(MediaInterface|string $media): ?MediaInterface
    {
        if ($media instanceof MediaInterface) {
            return $media;
        }

        $originalPath = 'uploads/' . $media;

        if (!isset($this->resolvedMedia[$originalPath])) {
            $this->resolvedMedia[$originalPath] = $this->mediaRepository->findByOriginalPath($originalPath) ?? false;
        }

        $resolved = $this->resolvedMedia[$originalPath];

        return $resolved instanceof MediaInterface ? $resolved : null;
    }
}
