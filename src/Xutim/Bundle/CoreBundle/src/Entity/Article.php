<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Entity;

use DateTimeImmutable;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\Criteria;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToMany;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\OneToMany;
use Doctrine\ORM\Mapping\OneToOne;
use Doctrine\ORM\Mapping\OrderBy;
use Symfony\Component\Uid\Uuid;
use Xutim\CoreBundle\Config\Layout\Layout;
use Xutim\CoreBundle\Exception\LogicException;
use Xutim\CoreBundle\Repository\ArticleRepository;

#[Entity(repositoryClass: ArticleRepository::class)]
class Article
{
    use TimestampableTrait;
    use FileTrait;
    use ArchiveStatusTrait;
    /** @use TranslatableTrait<ContentTranslation> */
    use TranslatableTrait;

    #[Id]
    #[Column(type: 'uuid')]
    private Uuid $id;

    #[Column(type: Types::STRING, nullable: true)]
    private ?string $layout;

    #[ManyToOne(targetEntity: Page::class, inversedBy: 'articles')]
    #[JoinColumn(nullable: false)]
    private Page $page;

    /** @var Collection<int, ContentTranslation> */
    #[OneToMany(mappedBy: 'article', targetEntity: ContentTranslation::class, indexBy: 'locale')]
    #[OrderBy(['locale' => 'ASC'])]
    private Collection $translations;

    #[OneToOne(targetEntity: ContentTranslation::class, cascade: ['persist', 'remove'], orphanRemoval: true)]
    #[JoinColumn(onDelete: 'SET NULL')]
    private ContentTranslation $defaultTranslation;

    /** @var Collection<int, Tag> */
    #[ManyToMany(targetEntity: Tag::class)]
    private Collection $tags;

    /**
     * @var Collection<int, File>
     */
    #[ManyToMany(targetEntity: File::class, mappedBy: 'articles')]
    #[OrderBy(['createdAt' => 'ASC'])]
    private Collection $files;

    /** @var Collection<int, BlockItem> */
    #[OneToMany(mappedBy: 'article', targetEntity: BlockItem::class)]
    private Collection $blockItems;

    #[Column(type: Types::DATETIME_IMMUTABLE, nullable: true)]
    private ?DateTimeImmutable $publishedAt;

    /**
     * @param Collection<int, Tag> $tags
     * @param array{}|array{
     *     time: int,
     *     blocks: array{}|array<array{id: string, type: string, data: array<string, mixed>}>,
     *     version: string
     * } $content
     */
    public function __construct(
        ?string $layout,
        string $preTitle,
        string $title,
        string $subTitle,
        string $slug,
        array $content,
        string $locale,
        string $description,
        Page $page,
        Collection $tags,
        ?int $spipId = null
    ) {
        $this->id = Uuid::v4();
        $this->layout = $layout;
        $this->createdAt = $this->updatedAt = new DateTimeImmutable();
        $this->page = $page;
        $this->tags = $tags;
        $this->blockItems = new ArrayCollection();
        $this->archived = false;

        $this->defaultTranslation = new ContentTranslation(
            $preTitle,
            $title,
            $subTitle,
            $slug,
            $content,
            $locale,
            $description,
            null,
            $this,
            $spipId
        );
        $this->translations = new ArrayCollection([$this->defaultTranslation]);
        $this->files = new ArrayCollection();
    }

    public function change(Page $page): void
    {
        $this->updatedAt = new DateTimeImmutable();
        $this->page = $page;
    }

    public function getId(): Uuid
    {
        return $this->id;
    }

    /**
     * @return Collection<int, ContentTranslation>
     */
    public function getTranslations(): Collection
    {
        return $this->translations;
    }

    public function getDefaultTranslation(): ContentTranslation
    {
        return $this->defaultTranslation;
    }

    public function setDefaultTranslation(ContentTranslation $trans): void
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

    public function getPage(): Page
    {
        return $this->page;
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

    public function getTranslationByLocaleOrDefault(string $locale): ContentTranslation
    {
        $criteria = Criteria::create()
            ->where(Criteria::expr()->eq('locale', $locale))
            ->setFirstResult(0)
            ->setMaxResults(1);
        /** @var ContentTranslation|false $translation */
        $translation = $this->translations->matching($criteria)->first();

        if ($translation === false) {
            return $this->defaultTranslation;
        }

        return $translation;
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

    public function setPublishedAt(?DateTimeImmutable $date): void
    {
        $this->publishedAt = $date;
    }

    public function getPublishedAt(): ?DateTimeImmutable
    {
        return $this->publishedAt;
    }

    public function canBePublished(): bool
    {
        if ($this->publishedAt === null) {
            return true;
        }
        $now = new DateTimeImmutable();

        return $this->publishedAt <= $now;
    }

    public function isPublishingScheduled(): bool
    {
        if ($this->publishedAt === null) {
            return false;
        }
        $now = new DateTimeImmutable();

        return $this->publishedAt > $now;
    }
}
