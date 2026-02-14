<?php

declare(strict_types=1);

namespace Xutim\MediaBundle\Twig\Components\Admin;

use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;
use Xutim\MediaBundle\Domain\Model\MediaInterface;
use Xutim\MediaBundle\Repository\MediaVariantRepositoryInterface;
use Xutim\MediaBundle\Service\PresetRegistry;
use Xutim\MediaBundle\Service\VariantPathResolver;

#[AsTwigComponent]
final class Picture
{
    public MediaInterface $media;
    public string $preset;
    public string $alt = '';
    public string $class = '';
    public string $sizes = '100vw';
    public string $loading = 'lazy';

    public function __construct(
        private readonly PresetRegistry $presetRegistry,
        private readonly VariantPathResolver $pathResolver,
        private readonly MediaVariantRepositoryInterface $variantRepository,
    ) {
    }

    public function getAvifSrcset(): string
    {
        return $this->buildSrcset('avif');
    }

    public function getWebpSrcset(): string
    {
        return $this->buildSrcset('webp');
    }

    public function getJpgSrcset(): string
    {
        return $this->buildSrcset('jpg');
    }

    public function getFallbackUrl(): string
    {
        $preset = $this->presetRegistry->get($this->preset);
        if ($preset === null) {
            return '';
        }

        $effectiveWidths = $preset->getEffectiveWidths();
        $lastWidth = end($effectiveWidths);
        $maxWidth = $lastWidth !== false ? $lastWidth : $preset->maxWidth;

        $variant = $this->variantRepository->findByMediaPresetFormatWidth(
            $this->media,
            $this->preset,
            'jpg',
            $maxWidth,
        );

        if ($variant !== null) {
            return $this->pathResolver->getUrl($this->media, $preset, $maxWidth, 'jpg', $variant->fingerprint());
        }

        $fingerprint = $this->pathResolver->calculateFingerprint($this->media, $preset, $maxWidth, 'jpg');

        return $this->pathResolver->getUrl($this->media, $preset, $maxWidth, 'jpg', $fingerprint);
    }

    public function getBlurHash(): ?string
    {
        return $this->media->blurHash();
    }

    private function buildSrcset(string $format): string
    {
        $preset = $this->presetRegistry->get($this->preset);
        if ($preset === null) {
            return '';
        }

        $variants = $this->variantRepository->findByMediaPresetFormat($this->media, $this->preset, $format);

        if ($variants === []) {
            return $this->buildFallbackSrcset($format);
        }

        $srcset = [];
        foreach ($variants as $variant) {
            $url = $this->pathResolver->getUrl(
                $this->media,
                $preset,
                $variant->width(),
                $format,
                $variant->fingerprint(),
            );
            $srcset[] = sprintf('%s %dw', $url, $variant->width());
        }

        return implode(', ', $srcset);
    }

    private function buildFallbackSrcset(string $format): string
    {
        $preset = $this->presetRegistry->get($this->preset);
        if ($preset === null) {
            return '';
        }

        $srcset = [];
        foreach ($preset->getEffectiveWidths() as $width) {
            $fingerprint = $this->pathResolver->calculateFingerprint($this->media, $preset, $width, $format);
            $url = $this->pathResolver->getUrl($this->media, $preset, $width, $format, $fingerprint);
            $srcset[] = sprintf('%s %dw', $url, $width);
        }

        return implode(', ', $srcset);
    }
}
