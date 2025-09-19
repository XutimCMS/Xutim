<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Entity;

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
use Xutim\CoreBundle\Domain\Model\FileInterface;
use Xutim\CoreBundle\Domain\Model\MediaFolderInterface;

#[MappedSuperclass]
class MediaFolder implements MediaFolderInterface
{
    use TimestampableTrait;

    #[Id]
    #[Column(type: 'uuid', unique: true, nullable: false)]
    private Uuid $id;

    #[Column(type: 'string', length: 255, nullable: false)]
    private string $name;

    #[ManyToOne(targetEntity: MediaFolderInterface::class, inversedBy: 'children')]
    private ?MediaFolderInterface $parent;

    /** @var Collection<int, MediaFolderInterface> */
    #[OneToMany(targetEntity: MediaFolderInterface::class, mappedBy: 'parent')]
    private Collection $children;

    /** @var Collection<int, FileInterface> */
    #[OneToMany(targetEntity: FileInterface::class, mappedBy: 'mediaFolder')]
    #[JoinColumn(nullable: true)]
    private Collection $files;

    public function __construct(
        string $name,
        ?MediaFolderInterface $parent
    ) {
        $this->id = Uuid::v4();
        $this->name = $name;
        $this->parent = $parent;
        $this->files = new ArrayCollection();
        $this->children = new ArrayCollection();
        $this->createdAt = new DateTimeImmutable();
        $this->updatedAt = new DateTimeImmutable();
    }

    public function change(string $name, ?MediaFolderInterface $parent): void
    {
        $this->name = $name;
        $this->parent = $parent;
    }

    public function getId(): Uuid
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getParent(): ?MediaFolderInterface
    {
        return $this->parent;
    }

    /**
     * @return Collection<int, FileInterface>
     */
    public function getFiles(): Collection
    {
        return $this->files;
    }

    /**
     * @return array<int, MediaFolderInterface>
     */
    public function getFolderPath(): array
    {
        $path = [];
        $parent = $this;
        while ($parent !== null) {
            $path[] = $parent;
            $parent = $parent->getParent();
        }

        return array_reverse($path);
    }

    /**
     * @return Collection<int, MediaFolderInterface>
    */
    public function getChildren(): Collection
    {
        return $this->children;
    }
}
