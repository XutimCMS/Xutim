<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\OneToMany;
use Doctrine\ORM\Mapping\OrderBy;
use Gedmo\Mapping\Annotation\SortableGroup;
use Gedmo\Mapping\Annotation\SortablePosition;
use Symfony\Component\Uid\Uuid;
use Webmozart\Assert\Assert;
use Xutim\CoreBundle\Form\Admin\Dto\MenuItemDto;
use Xutim\CoreBundle\Repository\MenuItemRepository;

#[Entity(repositoryClass: MenuItemRepository::class)]
class MenuItem
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
    private ?Page $page;

    #[ManyToOne]
    private ?Article $article;

    #[SortableGroup]
    #[ManyToOne(targetEntity: MenuItem::class, inversedBy: 'children')]
    private ?MenuItem $parent;

    /** @var Collection<int, MenuItem> */
    #[OneToMany(mappedBy: 'parent', targetEntity: MenuItem::class)]
    #[OrderBy(['position' => 'ASC'])]
    private Collection $children;

    public function __construct(?MenuItem $parent, bool $hasLink, ?Page $page, ?Article $article)
    {
        $this->id = Uuid::v4();
        $this->hasLink = $hasLink;
        $this->page = $page;
        $this->article = $article;
        $this->parent = $parent;
        $this->children = new ArrayCollection();
    }

    public function change(bool $hasLink, ?Page $page, ?Article $article): void
    {
        $this->hasLink = $hasLink;
        $this->page = $page;
        $this->article = $article;
    }

    public function getId(): Uuid
    {
        return $this->id;
    }

    public function hasLink(): bool
    {
        return $this->hasLink;
    }

    public function getParent(): ?MenuItem
    {
        return $this->parent;
    }

    public function getPage(): ?Page
    {
        return $this->page;
    }

    public function getObject(): Page|Article
    {
        if ($this->page === null) {
            Assert::notNull($this->article);

            return $this->article;
        }

        return $this->page;
    }

    public function getObjectTranslation(?string $locale): ContentTranslation
    {
        if ($this->page === null) {
            return $this->getArticleTranslation($locale);
        }

        return $this->getPageTranslation($locale);
    }

    public function getPageTranslation(?string $locale): ContentTranslation
    {
        Assert::notNull($this->page);

        if ($locale === null) {
            return $this->page->getDefaultTranslation();
        }
        return $this->page->getTranslationByLocaleOrDefault($locale);
    }

    public function getArticleTranslation(?string $locale): ContentTranslation
    {
        Assert::notNull($this->article);

        if ($locale === null) {
            return $this->article->getDefaultTranslation();
        }
        return $this->article->getTranslationByLocaleOrDefault($locale);
    }

    public function getArticle(): ?Article
    {
        return $this->article;
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

    public function toDto(): MenuItemDto
    {
        return new MenuItemDto(
            $this->hasLink,
            $this->page,
            $this->article
        );
    }
}
