<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\MappedSuperclass;
use Doctrine\ORM\Mapping\OneToMany;
use Doctrine\ORM\Mapping\OrderBy;
use Gedmo\Mapping\Annotation\SortableGroup;
use Gedmo\Mapping\Annotation\SortablePosition;
use Symfony\Component\Uid\Uuid;
use Webmozart\Assert\Assert;
use Xutim\CoreBundle\Domain\Model\ArticleInterface;
use Xutim\CoreBundle\Domain\Model\ContentTranslationInterface;
use Xutim\CoreBundle\Domain\Model\MenuItemInterface;
use Xutim\CoreBundle\Domain\Model\PageInterface;
use Xutim\CoreBundle\Domain\Model\SnippetInterface;
use Xutim\CoreBundle\Domain\Model\TagInterface;
use Xutim\CoreBundle\Domain\Model\TagTranslationInterface;
use Xutim\CoreBundle\Form\Admin\Dto\MenuItemDto;

#[MappedSuperclass]
class MenuItem implements MenuItemInterface
{
    #[Id]
    #[Column(type: 'uuid')]
    private Uuid $id;

    #[SortablePosition]
    #[Column(type: Types::INTEGER, nullable: false)]
    private int $position;

    #[Column(type: Types::BOOLEAN, nullable: false)]
    private bool $hasLink;

    #[ManyToOne]
    private ?PageInterface $page;

    #[ManyToOne]
    private ?TagInterface $tag;

    #[ManyToOne]
    private ?PageInterface $overwritePage;

    #[ManyToOne]
    private ?SnippetInterface $snippetAnchor;

    #[ManyToOne]
    private ?ArticleInterface $article;

    #[SortableGroup]
    #[ManyToOne(targetEntity: MenuItemInterface::class, inversedBy: 'children')]
    private ?MenuItemInterface $parent;

    /** @var Collection<int, MenuItemInterface> */
    #[OneToMany(mappedBy: 'parent', targetEntity: MenuItemInterface::class)]
    #[OrderBy(['position' => 'ASC'])]
    private Collection $children;

    public function __construct(
        ?MenuItemInterface $parent,
        bool $hasLink,
        ?PageInterface $page,
        ?ArticleInterface $article,
        ?TagInterface $tag,
        ?PageInterface $overwritePage,
        ?SnippetInterface $snippetAnchor
    ) {
        $this->id = Uuid::v4();
        $this->change($hasLink, $page, $article, $tag, $overwritePage, $snippetAnchor);
        $this->parent = $parent;
        $this->children = new ArrayCollection();
        $this->position = -1;
    }

    public function change(
        bool $hasLink,
        ?PageInterface $page,
        ?ArticleInterface $article,
        ?TagInterface $tag,
        ?PageInterface $overwritePage,
        ?SnippetInterface $snippetAnchor
    ): void {
        Assert::count(
            array_filter([$page, $article, $tag], static fn ($value) => $value !== null),
            1,
            'Exactly one of $page, $article or $tag must be set.'
        );
        $this->hasLink = $hasLink;
        $this->page = $page;
        $this->article = $article;
        $this->tag = $tag;
        $this->overwritePage = $overwritePage;
        $this->snippetAnchor = $snippetAnchor;
    }

    public function getId(): Uuid
    {
        return $this->id;
    }

    public function hasLink(): bool
    {
        return $this->hasLink;
    }

    public function getParent(): ?MenuItemInterface
    {
        return $this->parent;
    }

    public function getPage(): ?PageInterface
    {
        return $this->page;
    }

    public function getObject(): PageInterface|ArticleInterface|TagInterface
    {
        if ($this->hasPage() === true) {
            /** @var PageInterface */
            return $this->page;
        }
        if ($this->hasArticle() === true) {
            /** @var ArticleInterface */
            return $this->article;
        }

        Assert::notNull(
            $this->tag,
            'Exactly one of $page, $article or $tag must be set.'
        );
        return $this->tag;
    }

    public function getObjectTranslation(?string $locale): ContentTranslationInterface|TagTranslationInterface
    {
        if ($this->hasPage()) {
            return $this->getPageTranslation($locale);
        }

        if ($this->hasArticle()) {
            return $this->getArticleTranslation($locale);
        }

        return $this->getTagTranslation($locale);
    }

    public function getPageTranslation(?string $locale): ContentTranslationInterface
    {
        Assert::notNull($this->page);

        if ($locale === null) {
            return $this->page->getDefaultTranslation();
        }
        return $this->page->getTranslationByLocaleOrDefault($locale);
    }

    public function getArticleTranslation(?string $locale): ContentTranslationInterface
    {
        Assert::notNull($this->article);

        if ($locale === null) {
            return $this->article->getDefaultTranslation();
        }
        return $this->article->getTranslationByLocaleOrDefault($locale);
    }

    public function getArticle(): ?ArticleInterface
    {
        return $this->article;
    }

    public function getTagTranslation(?string $locale): TagTranslationInterface
    {
        Assert::notNull($this->tag);

        if ($locale === null) {
            $trans = $this->tag->getTranslations()->first();
            Assert::notFalse($trans);
            return $trans;
        }
        return $this->tag->getTranslationByLocaleOrAny($locale);
    }

    public function getTag(): ?TagInterface
    {
        return $this->tag;
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

    public function hasArticle(): bool
    {
        return $this->article !== null;
    }

    public function hasPage(): bool
    {
        return $this->page !== null;
    }

    public function hasTag(): bool
    {
        return $this->tag !== null;
    }

    /**
     * @phpstan-assert-if-true PageInterface $this->overwritePage
     * @phpstan-assert-if-false null $this->overwritePage
     */
    public function ovewritesPage(): bool
    {
        return $this->overwritePage !== null;
    }

    public function getOverwritePage(): ?PageInterface
    {
        return $this->overwritePage;
    }

    /**
     * @phpstan-assert-if-true SnippetInterface $this->snippetAnchor
     * @phpstan-assert-if-false null $this->snippetAnchor
     */
    public function hasSnippetAnchor(): bool
    {
        return $this->snippetAnchor !== null;
    }

    public function getSnippetAnchor(): ?SnippetInterface
    {
        return $this->snippetAnchor;
    }

    public function toDto(): MenuItemDto
    {
        return new MenuItemDto(
            $this->hasLink,
            $this->page,
            $this->article,
            $this->tag,
            $this->overwritePage,
            $this->snippetAnchor
        );
    }
}
