<?php

declare(strict_types=1);

namespace Xutim\MediaBundle\Domain\Model;

use DateTimeImmutable;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\Index;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\MappedSuperclass;
use Symfony\Component\Uid\Uuid;

#[MappedSuperclass()]
#[Index(columns: ['media_id', 'preset', 'format', 'width'], name: 'idx_media_variant_lookup')]
class MediaVariant implements MediaVariantInterface
{
    #[Id]
    #[Column(type: 'uuid')]
    private Uuid $id;

    #[ManyToOne(targetEntity: MediaInterface::class)]
    #[JoinColumn(name: 'media_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    private MediaInterface $media;

    #[Column(length: 64, options: ['comment' => 'Logical profile key, e.g. "front_header"'])]
    private string $preset;

    #[Column(length: 10, options: ['comment' => '"webp" | "avif" | "jpg" | "jpeg" | "png" | "gif"'])]
    private string $format;

    #[Column]
    private int $width;

    #[Column]
    private int $height;

    #[Column(length: 512, options: ['comment' => 'Storage-relative path to the generated file'])]
    private string $path;

    #[Column(length: 128, options: ['comment' => 'hash(sourceHash + normalizedRecipe) for cache busting'])]
    private string $fingerprint;

    #[Column(type: 'datetime_immutable')]
    private DateTimeImmutable $createdAt;

    public function __construct(
        MediaInterface $media,
        string $preset,
        string $format,
        int $width,
        int $height,
        string $path,
        string $fingerprint,
    ) {
        $this->id = Uuid::v4();
        $this->media = $media;
        $this->preset = $preset;
        $this->format = strtolower($format);
        $this->width = $width;
        $this->height = $height;
        $this->path = ltrim($path, '/');
        $this->fingerprint = $fingerprint;
        $this->createdAt = new DateTimeImmutable();
    }

    public function id(): Uuid
    {
        return $this->id;
    }

    public function media(): MediaInterface
    {
        return $this->media;
    }

    public function preset(): string
    {
        return $this->preset;
    }

    public function format(): string
    {
        return $this->format;
    }

    public function width(): int
    {
        return $this->width;
    }

    public function height(): int
    {
        return $this->height;
    }

    public function path(): string
    {
        return $this->path;
    }

    public function fingerprint(): string
    {
        return $this->fingerprint;
    }

    public function createdAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }
}
