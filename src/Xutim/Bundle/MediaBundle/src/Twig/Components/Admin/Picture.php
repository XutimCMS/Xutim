<?php

declare(strict_types=1);

namespace Xutim\MediaBundle\Twig\Components\Admin;

use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;
use Xutim\MediaBundle\Domain\Model\MediaInterface;
use Xutim\MediaBundle\Repository\MediaVariantRepositoryInterface;
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

    public function hasVariants(): bool
    {
        return $this->variantRepository->findByMediaPreset($this->media, $this->preset) !== [];
    }

    public function getFallbackUrl(): string
    {
        $variants = $this->variantRepository->findByMediaPresetFormat($this->media, $this->preset, 'jpg');

        if ($variants !== []) {
            $last = end($variants);

            return $this->pathResolver->getUrl($last);
        }

        return '';
    }

    public function getBlurHash(): ?string
    {
        return $this->media->blurHash();
    }

    private function buildSrcset(string $format): string
    {
        $variants = $this->variantRepository->findByMediaPresetFormat($this->media, $this->preset, $format);

        if ($variants === []) {
            return '';
        }

        $srcset = [];
        foreach ($variants as $variant) {
            $url = $this->pathResolver->getUrl($variant);
            $srcset[] = sprintf('%s %dw', $url, $variant->width());
        }

        return implode(', ', $srcset);
    }
}
