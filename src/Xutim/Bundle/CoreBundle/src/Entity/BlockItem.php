<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Entity;

use DateTimeImmutable;
use Deprecated;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Embedded;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Gedmo\Mapping\Annotation\SortableGroup;
use Gedmo\Mapping\Annotation\SortablePosition;
use Symfony\Component\Uid\Uuid;
use Xutim\CoreBundle\Form\Admin\Dto\ArticleBlockItemDto;
use Xutim\CoreBundle\Form\Admin\Dto\PageBlockItemDto;
use Xutim\CoreBundle\Form\Admin\Dto\SimpleBlockDto;
use Xutim\CoreBundle\Model\Coordinates;
use Xutim\CoreBundle\Repository\BlockItemRepository;

#[Entity(repositoryClass: BlockItemRepository::class)]
class BlockItem
{
    use TimestampableTrait;

    #[Id]
    #[Column(type: 'uuid', unique: true, nullable: false)]
    private Uuid $id;

    #[SortablePosition]
    #[Column(type: Types::INTEGER, nullable: false)]
    private int $position;

    #[Column(type: Types::STRING, length: 255, nullable: true)]
    private ?string $link;

    #[Embedded(class: Color::class)]
    private Color $color;

    #[Column(type: Types::STRING, length: 255, nullable: true)]
    private ?string $fileDescription;

    #[Column(type: 'decimal', precision: 10, scale: 6, nullable: true)]
    private ?string $latitude = null;

    #[Column(type: 'decimal', precision: 10, scale: 6, nullable: true)]
    private ?string $longitude = null;

    #[SortableGroup]
    #[ManyToOne(targetEntity: Block::class, inversedBy: 'blockItems')]
    #[JoinColumn(nullable: false)]
    private Block $block;

    #[ManyToOne(targetEntity: File::class, inversedBy: 'blockItems')]
    #[JoinColumn(nullable: true)]
    private ?File $file;

    #[ManyToOne(targetEntity: Page::class, inversedBy: 'blockItems')]
    #[JoinColumn(nullable: true)]
    private ?Page $page;

    #[ManyToOne(targetEntity: Article::class, inversedBy: 'blockItems')]
    #[JoinColumn(nullable: true)]
    private ?Article $article;

    #[ManyToOne(targetEntity: Snippet::class)]
    #[JoinColumn(nullable: true)]
    private ?Snippet $snippet;

    public function __construct(
        Block $block,
        ?Page $page,
        ?Article $article,
        ?File $file,
        ?Snippet $snippet = null,
        ?int $position = null,
        ?string $link = null,
        ?string $colorHex = null,
        ?string $fileDescription = null,
        ?float $latitude = null,
        ?float $longitude = null,
    ) {
        $this->id = Uuid::v4();
        $this->block = $block;
        $block->addItem($this);
        $this->page = $page;
        $this->article = $article;
        $this->file = $file;
        $this->snippet = $snippet;
        $this->position = $position === null ? 0 : $position;
        $this->link = $link;
        $this->color = new Color($colorHex);
        $this->fileDescription = $fileDescription;
        $this->latitude = $latitude !== null ? (string)$latitude : null;
        $this->longitude = $latitude !== null ? (string)$longitude : null;
        $this->createdAt = new DateTimeImmutable();
        $this->updatedAt = new DateTimeImmutable();
    }

    public function change(
        ?Page $page,
        ?Article $article,
        ?File $file,
        ?Snippet $snippet,
        ?int $position,
        ?string $link,
        ?string $colorHex,
        ?string $fileDescription,
        ?float $latitude,
        ?float $longitude
    ): void {
        $this->page = $page;
        $this->article = $article;
        $this->file = $file;
        $this->snippet = $snippet;
        $this->position = $position === null ? 0 : $position;
        $this->link = $link;
        $this->color = new Color($colorHex);
        $this->fileDescription = $fileDescription;
        $this->latitude = $latitude !== null ? (string)$latitude : null;
        $this->longitude = $latitude !== null ? (string)$longitude : null;
    }

    public function getId(): Uuid
    {
        return $this->id;
    }

    public function changePosition(int $position): void
    {
        $this->position = $position;
    }

    public function getPosition(): int
    {
        return $this->position;
    }

    /**
     * @phpstan-assert-if-true File $this->file
     * @phpstan-assert-if-false null $this->file
     */
    public function hasFile(): bool
    {
        return $this->file !== null;
    }

    public function getFile(): ?File
    {
        return $this->file;
    }

    public function getLink(): ?string
    {
        return $this->link;
    }

    public function hasLink(): bool
    {
        return $this->link !== null and $this->link !== '';
    }

    public function getColor(): Color
    {
        if ($this->color->isSet()) {
            return $this->color;
        }
        if ($this->hasArticle() && $this->article->getPage()->getColor()->isSet() === true) {
            return $this->article->getPage()->getColor();
        }
        if ($this->hasPage() && $this->page->getColor()->isSet() !== true) {
            return $this->page->getColor();
        }

        return $this->color;
    }

    public function getFileDescription(): ?string
    {
        return $this->fileDescription;
    }

    public function getObject(): Page|Article|null
    {
        return $this->hasArticle() ? $this->article : $this->page;
    }

    #[Deprecated("use hasContentObject() instead")]
    public function hasObject(): bool
    {
        return $this->hasContentObject();
    }

    public function hasContentObject(): bool
    {
        return $this->hasArticle() || $this->hasPage();
    }

    public function getPage(): ?Page
    {
        return $this->page;
    }

    public function getArticle(): ?Article
    {
        return $this->article;
    }

    /**
     * @phpstan-assert-if-true Page $this->page
     * @phpstan-assert-if-false null $this->page
     */
    public function hasPage(): bool
    {
        return $this->page !== null;
    }

    /**
     * @phpstan-assert-if-true Article $this->article
     * @phpstan-assert-if-false null $this->article
     */
    public function hasArticle(): bool
    {
        return $this->article !== null;
    }

    public function getSnippet(): ?Snippet
    {
        return $this->snippet;
    }

    /**
     * @phpstan-assert-if-true Snippet $this->snippet
     * @phpstan-assert-if-false null $this->snippet
     */
    public function hasSnippet(): bool
    {
        return $this->snippet !== null;
    }

    /**
     * @phpstan-assert-if-true null $this->article
     * @phpstan-assert-if-true null $this->page
     * @phpstan-assert-if-false Article $this->article
     * @phpstan-assert-if-false Page $this->page
     */
    public function isSimpleItem(): bool
    {
        return $this->article === null && $this->page === null;
    }

    public function getBlock(): Block
    {
        return $this->block;
    }

    public function getCoordinates(): ?Coordinates
    {
        if ($this->latitude === null || $this->longitude === null) {
            return null;
        }

        return new Coordinates((float)$this->latitude, (float)$this->longitude);
    }

    public function getDto(?\Symfony\Component\HttpFoundation\File\File $file): PageBlockItemDto|ArticleBlockItemDto|SimpleBlockDto
    {
        if ($this->hasPage()) {
            return new PageBlockItemDto($this->page, $file, $this->snippet, $this->position, $this->link, $this->color, $this->fileDescription, $this->getCoordinates());
        }

        if ($this->hasArticle()) {
            return new ArticleBlockItemDto($this->article, $file, $this->snippet, $this->position, $this->link, $this->color, $this->fileDescription, $this->getCoordinates());
        }

        return new SimpleBlockDto($file, $this->snippet, $this->position, $this->link, $this->color, $this->fileDescription, $this->getCoordinates());
    }
}
