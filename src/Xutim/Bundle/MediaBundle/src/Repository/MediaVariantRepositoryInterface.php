<?php

declare(strict_types=1);

namespace Xutim\MediaBundle\Repository;

use Xutim\MediaBundle\Domain\Model\MediaInterface;
use Xutim\MediaBundle\Domain\Model\MediaVariantInterface;

interface MediaVariantRepositoryInterface
{
    public function findByMediaPresetFormatWidth(
        MediaInterface $media,
        string $preset,
        string $format,
        int $width,
    ): ?MediaVariantInterface;

    /**
     * @return list<MediaVariantInterface>
     */
    public function findByMediaPresetFormat(
        MediaInterface $media,
        string $preset,
        string $format,
    ): array;

    /**
     * @return list<MediaVariantInterface>
     */
    public function findByMediaPreset(
        MediaInterface $media,
        string $preset,
    ): array;

    /**
     * @return list<string>
     */
    public function findAllPaths(): array;

    public function save(MediaVariantInterface $variant, bool $flush = false): void;

    public function deleteByMedia(MediaInterface $media): void;
}
