<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Entity;

use DateTimeImmutable;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\Criteria;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Embedded;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToMany;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\OneToMany;
use Doctrine\ORM\Mapping\OneToOne;
use Doctrine\ORM\Mapping\OrderBy;
use Gedmo\Mapping\Annotation\SortableGroup;
use Gedmo\Mapping\Annotation\SortablePosition;
use Symfony\Component\Uid\Uuid;
use Xutim\CoreBundle\Config\Layout\Layout;
use Xutim\CoreBundle\Exception\LogicException;
use Xutim\CoreBundle\Repository\PageRepository;

#[Entity(repositoryClass: PageRepository::class)]
class Page
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

    /** @var list<string> */
    #[Column(type: Types::JSON, nullable: false)]
    private array $translationLocales;

    #[Embedded(class: Color::class)]
    private Color $color;

    #[SortablePosition]
    #[Column(type: Types::INTEGER, nullable: false)]
    private int $position;

    #[ManyToOne(targetEntity: Page::class)]
    #[JoinColumn(nullable: false)]
    private Page $rootParent;

    #[SortableGroup]
    #[ManyToOne(targetEntity: Page::class, inversedBy: 'children')]
    private ?Page $parent;

    /** @var Collection<int, Page> */
    #[OneToMany(mappedBy: 'parent', targetEntity: Page::class)]
    #[OrderBy(['position' => 'ASC'])]
    private Collection $children;

    /** @var Collection<string, ContentTranslation> */
    #[OneToMany(mappedBy: 'page', targetEntity: ContentTranslation::class, indexBy: 'locale')]
    #[OrderBy(['locale' => 'ASC'])]
    private Collection $translations;

    /** @var Collection<int, File> */
    #[ManyToMany(targetEntity: File::class, mappedBy: 'pages')]
    #[OrderBy(['createdAt' => 'ASC'])]
    private Collection $files;

    #[OneToOne(targetEntity: ContentTranslation::class, cascade: ['persist', 'remove'], orphanRemoval: true)]
    #[JoinColumn(onDelete: 'SET NULL')]
    private ContentTranslation $defaultTranslation;

    /** @var Collection<int, Article> */
    #[OneToMany(mappedBy: 'page', targetEntity: Article::class)]
    private Collection $articles;

    /** @var Collection<int, BlockItem> */
    #[OneToMany(mappedBy: 'page', targetEntity: BlockItem::class)]
    private Collection $blockItems;

    /**
     * @param list<string> $locales
     * @param array{}|array{
     *     time: int,
     *     blocks: array{}|array<array{id: string, type: string, data: array<string, mixed>}>,
     *     version: string
     * } $content
     */
    public function __construct(
        ?string $layout,
        ?string   $colorHex,
        array    $locales,
        string $preTitle,
        string   $title,
        string $subTitle,
        string   $slug,
        array   $content,
        string   $locale,
        string   $description,
        ?Page $parent,
        ?int     $spipId = null
    ) {
        $this->id = Uuid::v4();
        $this->layout = $layout;
        $this->createdAt = $this->updatedAt = new DateTimeImmutable();

        $this->color = new Color($colorHex);
        $this->translationLocales = $locales;
        $this->parent = $parent;
        $this->setRootParent($parent);
        $this->children = new ArrayCollection();
        $this->defaultTranslation = new ContentTranslation(
            $preTitle,
            $title,
            $subTitle,
            $slug,
            $content,
            $locale,
            $description,
            $this,
            null,
            $spipId
        );
        $this->translations = new ArrayCollection();
        $this->translations->add($this->defaultTranslation);
        $this->archived = false;
        $this->blockItems = new ArrayCollection();
        $this->articles = new ArrayCollection();
        $this->files = new ArrayCollection();
    }

    public function __toString(): string
    {
        return $this->defaultTranslation->getTitle();
    }

    /**
     * @param list<string> $locales
     */
    public function change(?string $colorHex, array $locales, ?Page $parent): void
    {
        $this->updatedAt = new DateTimeImmutable();
        $this->color = new Color($colorHex);
        $this->translationLocales = $locales;
        $this->parent = $parent;
        $this->setRootParent($parent);
    }

    private function setRootParent(?Page $parent): void
    {
        if ($parent === null) {
            $this->rootParent = $this;
        } else {
            $this->rootParent = $parent->getRootPage();
        }
    }

    public function changeParent(?Page $parent): void
    {
        $this->parent = $parent;
        $this->setRootParent($parent);
    }

    public function setDefaultTranslation(ContentTranslation $trans): void
    {
        if ($this->getTranslations()->contains($trans) === false) {
            throw new LogicException(sprintf(
                'Translation "%s" cannot be marked as default when it\'s not part of the the page "%s"',
                $trans->getId()->toRfc4122(),
                $this->getId()->toRfc4122()
            ));
        }

        if ($this->defaultTranslation->getId() === $trans->getId()) {
            throw new LogicException('Translation is already a default translation of the page');
        }

        $this->defaultTranslation = $trans;
        $this->updatedAt = new DateTimeImmutable();
    }

    public function getId(): Uuid
    {
        return $this->id;
    }

    public function getColor(): Color
    {
        if ($this->color->isSet() === false && $this->isRoot() === false) {
            return $this->getRootPage()->getColor();
        }

        return $this->color;
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

    /**
     * @return array<string, string>
     */
    public function getExistingTranslationLocales(): array
    {
        return $this->translations->map(fn (ContentTranslation $trans) => $trans->getLocale())->toArray();
    }

    public function getPublishedTranslationByLocale(string $locale): ?ContentTranslation
    {
        $translation = $this->getTranslationByLocale($locale);

        if ($translation !== null && $translation->isPublished() === false) {
            return null;
        }

        return $translation;
    }

    public function getDefaultTranslation(): ContentTranslation
    {
        return $this->defaultTranslation;
    }

    public function getRootPage(): Page
    {
        return $this->rootParent;
    }

    public function getParent(): ?Page
    {
        return $this->parent;
    }

    /**
     * @phpstan-assert-if-true null $this->parent
     * @phpstan-assert-if-false Page $this->parent
     */
    public function isRoot(): bool
    {
        return $this->parent === null;
    }

    /**
     * @return Collection<int, Page>
     */
    public function getChildren(): Collection
    {
        return $this->children;
    }

    /**
     * @return list<string>
     */
    public function getLocales(): array
    {
        return $this->translationLocales;
    }

    /**
     * @param string $locale
     * @return array<ContentTranslation>
     */
    public function getPublishedArticlesByLocale(string $locale): array
    {
        $filteredArticles = [];
        foreach ($this->articles->filter(fn (Article $article) => $article->getTranslationByLocale($locale)?->isPublished() === true) as $article) {
            $translation = $article->getTranslationByLocale($locale);
            if ($translation !== null) {
                $filteredArticles[] = $translation;
            }
        }

        return $filteredArticles;
    }

    /**
     * @param string $locale
     * @return array<ContentTranslation>
     */
    public function getUnPublishedArticlesByLocale(string $locale): array
    {
        $filteredArticles = [];
        foreach ($this->articles->filter(fn (Article $article) => $article->getTranslationByLocale($locale)?->isPublished() === false) as $article) {
            $translation = $article->getTranslationByLocale($locale);
            if ($translation !== null) {
                $filteredArticles[] = $translation;
            }
        }

        return $filteredArticles;
    }

    /**
     * @return Collection<string, ContentTranslation>
     */
    public function getTranslations(): Collection
    {
        return $this->translations;
    }

    /**
     * @return Collection<int, Article>
     */
    public function getArticles(): Collection
    {
        return $this->articles;
    }

    public function canBeDeleted(): bool
    {
        if ($this->articles->isEmpty() === false) {
            return false;
        }
        if ($this->blockItems->isEmpty() === false) {
            return false;
        }

        return $this->children->isEmpty();
    }

    public function prepareDeletion(): bool
    {
        if ($this->canBeDeleted() === false) {
            return false;
        }
        foreach ($this->files as $file) {
            $file->removePage($this);
            $this->removeFile($file);
        }

        $this->parent = null;

        return true;
    }

    public function getRootParent(): Page
    {
        return $this->rootParent;
    }

    public function getPosition(): int
    {
        return $this->position;
    }

    public function movePosUp(int $step): void
    {
        if ($this->position - $step < 0) {
            $this->position = 0;

            return;
        }
        $this->position -= $step;
    }

    public function movePosDown(int $step): void
    {
        $this->position += $step;
    }

    public function getLayout(): ?string
    {
        return $this->layout;
    }

    public function changeLayout(?Layout $layout): void
    {
        $this->layout = $layout?->code;
    }
}
