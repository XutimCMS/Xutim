<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Entity;

use DateTimeImmutable;
use Deprecated;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Embedded;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\MappedSuperclass;
use Gedmo\Mapping\Annotation\SortableGroup;
use Gedmo\Mapping\Annotation\SortablePosition;
use Symfony\Component\Uid\Uuid;
use Xutim\CoreBundle\Domain\Model\ArticleInterface;
use Xutim\CoreBundle\Domain\Model\BlockInterface;
use Xutim\CoreBundle\Domain\Model\BlockItemInterface;
use Xutim\CoreBundle\Domain\Model\Coordinates;
use Xutim\CoreBundle\Domain\Model\FileInterface;
use Xutim\CoreBundle\Domain\Model\MediaFolderInterface;
use Xutim\CoreBundle\Domain\Model\PageInterface;
use Xutim\CoreBundle\Domain\Model\TagInterface;
use Xutim\CoreBundle\Form\Admin\Dto\BlockItemDto;
use Xutim\SnippetBundle\Domain\Model\SnippetInterface;

#[MappedSuperclass]
class BlockItem implements BlockItemInterface
{
    use TimestampableTrait;

    #[Id]
    #[Column(type: 'uuid', unique: true, nullable: false)]
    private Uuid $id;

    #[SortablePosition]
    #[Column(type: Types::INTEGER, nullable: false)]
    private int $position;

    #[Column(type: Types::STRING, length: 255, nullable: true)]
    private ?string $text;

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
    #[ManyToOne(targetEntity: BlockInterface::class, inversedBy: 'blockItems')]
    #[JoinColumn(nullable: false)]
    private BlockInterface $block;

    #[ManyToOne(targetEntity: FileInterface::class, inversedBy: 'blockItems')]
    #[JoinColumn(nullable: true)]
    private ?FileInterface $file;

    #[ManyToOne(targetEntity: PageInterface::class, inversedBy: 'blockItems')]
    #[JoinColumn(nullable: true)]
    private ?PageInterface $page;

    #[ManyToOne(targetEntity: ArticleInterface::class, inversedBy: 'blockItems')]
    #[JoinColumn(nullable: true)]
    private ?ArticleInterface $article;

    #[ManyToOne(targetEntity: SnippetInterface::class)]
    #[JoinColumn(nullable: true)]
    private ?SnippetInterface $snippet;

    #[ManyToOne(targetEntity: TagInterface::class)]
    #[JoinColumn(nullable: true)]
    private ?TagInterface $tag;

    #[ManyToOne(targetEntity: MediaFolderInterface::class)]
    #[JoinColumn(nullable: true)]
    private ?MediaFolderInterface $mediaFolder;

    public function __construct(
        BlockInterface $block,
        ?PageInterface $page,
        ?ArticleInterface $article,
        ?FileInterface $file,
        ?SnippetInterface $snippet = null,
        ?TagInterface $tag = null,
        ?MediaFolderInterface $folder = null,
        ?string $text = null,
        ?string $link = null,
        ?string $colorHex = null,
        ?string $fileDescription = null,
        ?float $latitude = null,
        ?float $longitude = null,
    ) {
        $this->id = Uuid::v4();
        $this->block = $block;
        $block->addItem($this);
        $this->change(
            $page,
            $article,
            $file,
            $snippet,
            $tag,
            $folder,
            $text,
            $link,
            $colorHex,
            $fileDescription,
            $latitude,
            $longitude
        );
        $this->position = -1;
        $this->createdAt = new DateTimeImmutable();
        $this->updatedAt = new DateTimeImmutable();
    }

    public function change(
        ?PageInterface $page,
        ?ArticleInterface $article,
        ?FileInterface $file,
        ?SnippetInterface $snippet,
        ?TagInterface $tag,
        ?MediaFolderInterface $folder,
        ?string $text,
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
        $this->tag = $tag;
        $this->mediaFolder = $folder;
        $this->text = $text;
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
     * @phpstan-assert-if-true FileInterface $this->file
     * @phpstan-assert-if-false null $this->file
     */
    public function hasFile(): bool
    {
        return $this->file !== null;
    }

    public function getFile(): ?FileInterface
    {
        return $this->file;
    }

    public function getText(): ?string
    {
        return $this->text;
    }

    public function hasText(): bool
    {
        return $this->text !== null and $this->text !== '';
    }

    public function getLink(): ?string
    {
        return $this->link;
    }

    public function hasLink(): bool
    {
        return $this->link !== null and $this->link !== '';
    }

    public function hasColor(): bool
    {
        return $this->color->isSet();
    }

    public function getColor(): ?Color
    {
        if ($this->color->isSet()) {
            return $this->color;
        }

        return null;
    }

    public function getFileDescription(): ?string
    {
        return $this->fileDescription;
    }

    public function getObject(): PageInterface|ArticleInterface|null
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

    public function getPage(): ?PageInterface
    {
        return $this->page;
    }

    public function getArticle(): ?ArticleInterface
    {
        return $this->article;
    }

    /**
     * @phpstan-assert-if-true PageInterface $this->page
     * @phpstan-assert-if-false null $this->page
     */
    public function hasPage(): bool
    {
        return $this->page !== null;
    }

    /**
     * @phpstan-assert-if-true ArticleInterface $this->article
     * @phpstan-assert-if-false null $this->article
     */
    public function hasArticle(): bool
    {
        return $this->article !== null;
    }

    public function getSnippet(): ?SnippetInterface
    {
        return $this->snippet;
    }

    /**
     * @phpstan-assert-if-true SnippetInterface $this->snippet
     * @phpstan-assert-if-false null $this->snippet
     */
    public function hasSnippet(): bool
    {
        return $this->snippet !== null;
    }

    public function getTag(): ?TagInterface
    {
        return $this->tag;
    }

    /**
     * @phpstan-assert-if-true TagInterface $this->tag
     * @phpstan-assert-if-false null $this->tag
     */
    public function hasTag(): bool
    {
        return $this->tag !== null;
    }

    public function getMediaFolder(): ?MediaFolderInterface
    {
        return $this->mediaFolder;
    }

    /**
     * @phpstan-assert-if-true MediaFolderInterface $this->mediaFolder
     * @phpstan-assert-if-false null $this->mediaFolder
     */
    public function hasMediaFolder(): bool
    {
        return $this->mediaFolder !== null;
    }

    /**
     * @phpstan-assert-if-true null $this->article
     * @phpstan-assert-if-true null $this->page
     * @phpstan-assert-if-false ArticleInterface $this->article
     * @phpstan-assert-if-false PageInterface $this->page
     */
    public function isSimpleItem(): bool
    {
        return $this->article === null && $this->page === null;
    }

    public function getBlock(): BlockInterface
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

    public function getDto(): BlockItemDto
    {
        return new BlockItemDto(
            $this->page,
            $this->article,
            $this->file,
            $this->snippet,
            $this->tag,
            $this->mediaFolder,
            $this->position,
            $this->text,
            $this->link,
            $this->color->getHex(),
            $this->fileDescription,
            $this->getCoordinates()
        );
    }

    public function changeFile(FileInterface $file): void
    {
        $this->file = $file;
    }
}
