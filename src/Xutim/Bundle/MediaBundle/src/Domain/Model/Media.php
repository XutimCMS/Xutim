<?php

declare(strict_types=1);

namespace Xutim\MediaBundle\Domain\Model;

use DateTimeImmutable;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\MappedSuperclass;
use Doctrine\ORM\Mapping\OneToMany;
use Symfony\Component\Uid\Uuid;
use Xutim\CoreBundle\Entity\TimestampableTrait;

#[MappedSuperclass()]
class Media implements MediaInterface
{
    use TimestampableTrait;

    #[Id]
    #[Column(type: 'uuid')]
    private Uuid $id;

    /** @var Collection<int, MediaTranslationInterface> */
    #[OneToMany(targetEntity: MediaTranslationInterface::class, mappedBy: 'media')]
    private Collection $translations;

    #[ManyToOne(targetEntity: MediaFolderInterface::class)]
    #[JoinColumn(name: 'folder_id', referencedColumnName: 'id', nullable: true, onDelete: 'SET NULL')]
    private ?MediaFolderInterface $folder;

    // storage-relative original location, e.g. "media/articles/2025/08/15/abc.jpg"
    #[Column(length: 512)]
    private string $originalPath;

    #[Column(length: 10)]
    private string $originalExt;

    #[Column(length: 127)]
    private string $mime;

    // perceptual for images, sha256 otherwise
    #[Column(length: 128)]
    private string $hash;

    #[Column]
    private int $sizeBytes;

    #[Column(options: ['default' => 0])]
    private int $width = 0;

    #[Column(options: ['default' => 0])]
    private int $height = 0;

    #[Column(length: 255, nullable: true)]
    private ?string $copyright;

    // normalized [0..1]
    #[Column(type: 'float', nullable: true)]
    private ?float $focalX;

    #[Column(type: 'float', nullable: true)]
    private ?float $focalY;

    // BlurHash string for LQIP (Low Quality Image Placeholder)
    #[Column(length: 32, nullable: true)]
    private ?string $blurHash;

    public function __construct(
        ?MediaFolderInterface $folder,
        string $originalPath,
        string $originalExt,
        string $mime,
        string $hash,
        int $sizeBytes,
        int $width = 0,
        int $height = 0,
        ?string $copyright = null,
        ?float $focalX = null,
        ?float $focalY = null,
        ?string $blurHash = null,
    ) {
        $this->id = Uuid::v4();
        $now = new DateTimeImmutable();
        $this->createdAt = $now;
        $this->updatedAt = $now;
        $this->translations = new ArrayCollection();

        $this->folder = $folder;
        $this->originalPath = ltrim($originalPath, '/');
        $this->originalExt = strtolower($originalExt);
        $this->mime = strtolower($mime);
        $this->hash = $hash;
        $this->sizeBytes = $sizeBytes;
        $this->width = max(0, $width);
        $this->height = max(0, $height);
        $this->copyright = $copyright;
        $this->focalX = $focalX;
        $this->focalY = $focalY;
        $this->blurHash = $blurHash;
    }

    public function change(
        ?string $copyright = null,
        ?float $focalX = null,
        ?float $focalY = null,
        ?MediaFolderInterface $folder = null,
        ?string $blurHash = null,
    ): void {
        if ($copyright !== null) {
            $this->copyright = $copyright;
        }
        if ($focalX !== null) {
            $this->focalX = $focalX;
        }
        if ($focalY !== null) {
            $this->focalY = $focalY;
        }
        if ($folder !== null) {
            $this->folder = $folder;
        }
        if ($blurHash !== null) {
            $this->blurHash = $blurHash;
        }
        $this->updatedAt = new DateTimeImmutable();
    }

    public function changeFolder(?MediaFolderInterface $folder): void
    {
        $this->folder = $folder;
        $this->updatedAt = new DateTimeImmutable();
    }

    public function changeCopyright(string $copyright): void
    {
        $this->copyright = $copyright;
        $this->updatedAt = new DateTimeImmutable();
    }

    public function changeBlurHash(string $blurHash): void
    {
        $this->blurHash = $blurHash;
        $this->updatedAt = new DateTimeImmutable();
    }

    public function id(): Uuid
    {
        return $this->id;
    }

    public function folder(): ?MediaFolderInterface
    {
        return $this->folder;
    }

    public function originalPath(): string
    {
        return $this->originalPath;
    }

    public function originalExt(): string
    {
        return $this->originalExt;
    }

    public function mime(): string
    {
        return $this->mime;
    }

    public function hash(): string
    {
        return $this->hash;
    }

    public function sizeBytes(): int
    {
        return $this->sizeBytes;
    }

    public function width(): int
    {
        return $this->width;
    }

    public function height(): int
    {
        return $this->height;
    }

    public function copyright(): ?string
    {
        return $this->copyright;
    }

    public function focalX(): ?float
    {
        return $this->focalX;
    }

    public function focalY(): ?float
    {
        return $this->focalY;
    }

    public function blurHash(): ?string
    {
        return $this->blurHash;
    }

    public function isImage(): bool
    {
        return str_starts_with($this->mime, 'image/');
    }

    public function getTranslationByLocale(string $locale): ?MediaTranslationInterface
    {
        foreach ($this->translations as $translation) {
            if ($translation->locale() === $locale) {
                return $translation;
            }
        }

        return null;
    }

    public function addTranslation(MediaTranslationInterface $translation): void
    {
        if (!$this->translations->contains($translation)) {
            $this->translations->add($translation);
        }
    }

    /**
     * @return Collection<int, MediaTranslationInterface>
     */
    public function getTranslations(): Collection
    {
        return $this->translations;
    }
}
