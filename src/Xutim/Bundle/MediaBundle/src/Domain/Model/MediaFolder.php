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
class MediaFolder implements MediaFolderInterface
{
    use TimestampableTrait;

    #[Id]
    #[Column(type: 'uuid')]
    private Uuid $id;

    #[Column(length: 64)]
    private string $code;

    #[Column(length: 128)]
    private string $name;

    // base path under storage root, without leading slash, e.g. "media/articles"
    #[Column(length: 255)]
    private string $basePath;

    #[Column(options: ['default' => true])]
    private bool $active = true;

    #[ManyToOne(targetEntity: MediaFolderInterface::class, inversedBy: 'children')]
    #[JoinColumn(name: 'parent_id', referencedColumnName: 'id', nullable: true, onDelete: 'SET NULL')]
    private ?MediaFolderInterface $parent = null;

    /** @var Collection<int, MediaFolderInterface> */
    #[OneToMany(targetEntity: MediaFolderInterface::class, mappedBy: 'parent')]
    private Collection $children;

    /** @var Collection<int, MediaInterface> */
    #[OneToMany(targetEntity: MediaInterface::class, mappedBy: 'folder')]
    private Collection $media;

    public function __construct(
        string $code,
        string $name,
        string $basePath,
        ?MediaFolderInterface $parent = null,
    ) {
        $this->id = Uuid::v4();
        $this->createdAt = new DateTimeImmutable();
        $this->updatedAt = $this->createdAt;

        $this->code = $code;
        $this->name = $name;
        $this->basePath = trim($basePath, '/');
        $this->parent = $parent;
        $this->children = new ArrayCollection();
        $this->media = new ArrayCollection();
    }

    public function change(
        ?string $name = null,
        ?string $basePath = null,
        ?bool $active = null,
        ?MediaFolderInterface $parent = null,
    ): void {
        if ($name !== null) {
            $this->name = $name;
        }
        if ($basePath !== null) {
            $this->basePath = trim($basePath, '/');
        }
        if ($active !== null) {
            $this->active = $active;
        }
        if ($parent !== null) {
            $this->parent = $parent;
        }
        $this->updatedAt = new DateTimeImmutable();
    }

    public function changeName(string $name): void
    {
        $this->name = $name;
        $this->updatedAt = new DateTimeImmutable();
    }

    public function id(): Uuid
    {
        return $this->id;
    }

    public function code(): string
    {
        return $this->code;
    }

    public function name(): string
    {
        return $this->name;
    }

    public function basePath(): string
    {
        return $this->basePath;
    }

    public function isActive(): bool
    {
        return $this->active;
    }

    public function parent(): ?MediaFolderInterface
    {
        return $this->parent;
    }

    /**
     * @return Collection<int, MediaFolderInterface>
     */
    public function children(): Collection
    {
        return $this->children;
    }

    /**
     * @return Collection<int, MediaInterface>
     */
    public function media(): Collection
    {
        return $this->media;
    }

    /**
     * @return array<int, MediaFolderInterface>
     */
    public function folderPath(): array
    {
        $path = [];
        $current = $this;
        while ($current !== null) {
            $path[] = $current;
            $current = $current->parent();
        }

        return array_reverse($path);
    }

    public function createdAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function updatedAt(): DateTimeImmutable
    {
        return $this->updatedAt;
    }
}
