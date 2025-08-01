<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Entity;

use DateTimeImmutable;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\InverseJoinColumn;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\JoinTable;
use Doctrine\ORM\Mapping\ManyToMany;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\MappedSuperclass;
use Doctrine\ORM\Mapping\OneToMany;
use Doctrine\ORM\Mapping\OneToOne;
use Doctrine\ORM\Mapping\OrderBy;
use Symfony\Component\Uid\Uuid;
use Xutim\CoreBundle\Config\Layout\Layout;
use Xutim\CoreBundle\Domain\Model\ArticleInterface;
use Xutim\CoreBundle\Domain\Model\BlockItemInterface;
use Xutim\CoreBundle\Domain\Model\ContentTranslationInterface;
use Xutim\CoreBundle\Domain\Model\FileInterface;
use Xutim\CoreBundle\Domain\Model\TagInterface;
use Xutim\CoreBundle\Exception\LogicException;

#[MappedSuperclass]
class Article implements ArticleInterface
{
    use TimestampableTrait;
    use FileTrait;
    use ArchiveStatusTrait;

    /** @use BasicTranslatableTrait<ContentTranslationInterface> */
    use BasicTranslatableTrait;

    /** @use PublishableTranslatableTrait<string, ContentTranslationInterface> */
    use PublishableTranslatableTrait;

    #[Id]
    #[Column(type: 'uuid')]
    private Uuid $id;

    #[Column(type: Types::STRING, nullable: true)]
    private ?string $layout;

    #[ManyToOne(targetEntity: FileInterface::class, inversedBy: 'featuredArticles')]
    private ?FileInterface $featuredImage;

    /** @var Collection<string, ContentTranslationInterface> */
    #[OneToMany(mappedBy: 'article', targetEntity: ContentTranslationInterface::class, indexBy: 'locale')]
    #[OrderBy(['locale' => 'ASC'])]
    private Collection $translations;

    #[OneToOne(targetEntity: ContentTranslationInterface::class, cascade: ['persist', 'remove'], orphanRemoval: true)]
    #[JoinColumn(onDelete: 'SET NULL')]
    private ContentTranslationInterface $defaultTranslation;

    /** @var Collection<int, TagInterface> */
    #[ManyToMany(targetEntity: TagInterface::class, inversedBy: 'articles')]
    #[JoinTable(name: 'xutim_article_tag')]
    #[JoinColumn(name: 'article_id')]
    #[InverseJoinColumn(name: 'tag_id')]
    private Collection $tags;

    /**
     * @var Collection<int, FileInterface>
     */
    #[ManyToMany(targetEntity: FileInterface::class, mappedBy: 'articles')]
    #[OrderBy(['createdAt' => 'ASC'])]
    private Collection $files;

    /** @var Collection<int, BlockItemInterface> */
    #[OneToMany(mappedBy: 'article', targetEntity: BlockItemInterface::class)]
    private Collection $blockItems;

    #[Column(type: Types::DATETIME_IMMUTABLE, nullable: true)]
    private ?DateTimeImmutable $scheduledAt;

    /**
     * @param Collection<int, TagInterface> $tags
     */
    public function __construct(
        ?string $layout,
        Collection $tags,
        ?FileInterface $featuredImage
    ) {
        $this->id = Uuid::v4();
        $this->layout = $layout;
        $this->createdAt = $this->updatedAt = new DateTimeImmutable();
        $this->tags = $tags;
        $this->blockItems = new ArrayCollection();
        $this->translations = new ArrayCollection();
        $this->archived = false;
        $this->featuredImage = $featuredImage;
        $this->files = new ArrayCollection();
        $this->scheduledAt = null;
    }

    public function change(): void
    {
        $this->updatedAt = new DateTimeImmutable();
    }

    public function addTranslation(ContentTranslationInterface $trans): void
    {
        if ($this->translations->contains($trans) === true) {
            return;
        }
        $this->translations->add($trans);
        if (count($this->translations) === 1) {
            $this->defaultTranslation = $trans;
        }
    }

    public function getId(): Uuid
    {
        return $this->id;
    }

    /**
     * @return Collection<string, ContentTranslationInterface>
     */
    public function getTranslations(): Collection
    {
        return $this->translations;
    }

    public function getDefaultTranslation(): ContentTranslationInterface
    {
        return $this->defaultTranslation;
    }

    public function setDefaultTranslation(ContentTranslationInterface $trans): void
    {
        if ($this->getTranslations()->contains($trans) === false) {
            throw new LogicException(sprintf(
                'Translation "%s" cannot be marked as default when it\'s not part of the the article "%s"',
                $trans->getId()->toRfc4122(),
                $this->getId()->toRfc4122()
            ));
        }

        if ($this->defaultTranslation->getId() === $trans->getId()) {
            throw new LogicException('Translation is already a default translation of the article');
        }

        $this->defaultTranslation = $trans;
        $this->updatedAt = new DateTimeImmutable();
    }

    /**
     * @return array{total: int, translated: int}
     */
    public function getTranslationStats(): array
    {
        $total = count($this->translations);
        $translated = 0;

        foreach ($this->translations as $translation) {
            if ($translation->isPublished()) {
                $translated++;
            }
        }

        return ['total' => $total, 'translated' => $translated];
    }

    public function getTitle(): string
    {
        return $this->defaultTranslation->getTitle();
    }

    public function getLayout(): ?string
    {
        return $this->layout;
    }
    
    public function changeLayout(?Layout $layout): void
    {
        $this->layout = $layout?->code;
    }


    public function canBeDeleted(): bool
    {
        if ($this->blockItems->isEmpty() === false) {
            return false;
        }

        return true;
    }

    public function prepareDeletion(): bool
    {
        if ($this->canBeDeleted() === false) {
            return false;
        }
        foreach ($this->files as $file) {
            $file->removeArticle($this);
            $this->removeFile($file);
        }

        return true;
    }

    public function setScheduledAt(?DateTimeImmutable $date): void
    {
        $this->scheduledAt = $date;
    }

    public function getScheduledAt(): ?DateTimeImmutable
    {
        return $this->scheduledAt;
    }

    public function canBePublished(): bool
    {
        if ($this->scheduledAt === null) {
            return true;
        }
        $now = new DateTimeImmutable();

        return $this->scheduledAt <= $now;
    }

    public function isPublishingScheduled(): bool
    {
        if ($this->scheduledAt === null) {
            return false;
        }
        $now = new DateTimeImmutable();

        return $this->scheduledAt > $now;
    }

    public function getFeaturedImage(): ?FileInterface
    {
        return $this->featuredImage;
    }

    public function changeFeaturedImage(?FileInterface $image): void
    {
        $this->featuredImage = $image;
    }

    public function hasFeaturedImage(): bool
    {
        return $this->featuredImage !== null;
    }

    /**
     * @return Collection<int,TagInterface>
    */
    public function getTags(): Collection
    {
        return $this->tags;
    }

    public function hasTag(TagInterface $tag): bool
    {
        return $this->tags->contains($tag);
    }

    public function addTag(TagInterface $tag): void
    {
        $this->tags->add($tag);
    }

    public function removeTag(TagInterface $tag): void
    {
        $this->tags->removeElement($tag);
    }
}
