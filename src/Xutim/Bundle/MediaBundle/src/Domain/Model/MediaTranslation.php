<?php

declare(strict_types=1);

namespace Xutim\MediaBundle\Domain\Model;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\MappedSuperclass;
use Symfony\Component\Uid\Uuid;

#[MappedSuperclass()]
class MediaTranslation implements MediaTranslationInterface
{
    #[Id]
    #[Column(type: 'uuid')]
    private Uuid $id;

    #[ManyToOne(targetEntity: MediaInterface::class)]
    #[JoinColumn(name: 'media_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    private MediaInterface $media;

    #[Column(length: 16)]
    private string $locale;

    #[Column(length: 255)]
    private string $name;

    #[Column(length: 255)]
    private string $alt;

    public function __construct(MediaInterface $media, string $locale, string $name, string $alt)
    {
        $this->id = Uuid::v4();
        $this->media = $media;
        $this->locale = $locale;
        $this->name = $name;
        $this->alt = $alt;
    }

    public function id(): Uuid
    {
        return $this->id;
    }

    public function media(): MediaInterface
    {
        return $this->media;
    }

    public function locale(): string
    {
        return $this->locale;
    }

    public function name(): string
    {
        return $this->name;
    }

    public function alt(): string
    {
        return $this->alt;
    }

    public function change(string $name, string $alt): void
    {
        $this->name = $name;
        $this->alt = $alt;
    }
}
