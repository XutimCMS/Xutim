<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Entity;

use DateTimeImmutable;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Embedded;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\MappedSuperclass;
use Doctrine\ORM\Mapping\OneToMany;
use Doctrine\ORM\Mapping\OneToOne;
use Doctrine\ORM\Mapping\OrderBy;
use Gedmo\Mapping\Annotation\SortableGroup;
use Gedmo\Mapping\Annotation\SortablePosition;
use Symfony\Component\Uid\Uuid;
use Xutim\CoreBundle\Config\Layout\Layout;
use Xutim\CoreBundle\Domain\Model\BlockItemInterface;
use Xutim\CoreBundle\Domain\Model\ContentTranslationInterface;
use Xutim\CoreBundle\Domain\Model\PageInterface;
use Xutim\CoreBundle\Exception\LogicException;
use Xutim\MediaBundle\Domain\Model\MediaInterface;

#[MappedSuperclass]
class Page implements PageInterface
{
    use TimestampableTrait;
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

    /** @var list<string> */
    #[Column(type: Types::JSON, nullable: false)]
    private array $translationLocales;

    #[Embedded(class: Color::class)]
    private Color $color;

    #[SortablePosition]
    #[Column(type: Types::INTEGER, nullable: false)]
    private int $position;

    #[ManyToOne(targetEntity: MediaInterface::class)]
    #[JoinColumn(name: 'featured_image')]
    private ?MediaInterface $featuredImage;

    #[ManyToOne(targetEntity: PageInterface::class)]
    #[JoinColumn(name: 'root_parent', nullable: false)]
    private PageInterface $rootParent;

    #[SortableGroup]
    #[ManyToOne(targetEntity: PageInterface::class, inversedBy: 'children')]
    private ?PageInterface $parent;

    /** @var Collection<int, PageInterface> */
    #[OneToMany(mappedBy: 'parent', targetEntity: PageInterface::class)]
    #[OrderBy(['position' => 'ASC'])]
    private Collection $children;

    /** @var Collection<string, ContentTranslationInterface> */
    #[OneToMany(mappedBy: 'page', targetEntity: ContentTranslationInterface::class, indexBy: 'locale')]
    #[OrderBy(['locale' => 'ASC'])]
    private Collection $translations;

    #[OneToOne(targetEntity: ContentTranslationInterface::class, cascade: ['persist', 'remove'], orphanRemoval: true)]
    #[JoinColumn(onDelete: 'SET NULL')]
    private ContentTranslationInterface $defaultTranslation;

    /** @var Collection<int, BlockItemInterface> */
    #[OneToMany(mappedBy: 'page', targetEntity: BlockItemInterface::class)]
    private Collection $blockItems;

    /**
     * @param list<string> $locales
     */
    public function __construct(
        ?string $layout,
        array $locales,
        ?PageInterface $parent,
        ?MediaInterface $featuredImage
    ) {
        $this->id = Uuid::v4();
        $this->layout = $layout;
        $this->color = new Color('ef7d01');
        $this->translationLocales = $locales;
        $this->parent = $parent;
        $this->setRootParent($parent);
        $this->featuredImage = $featuredImage;
        $this->archived = false;
        $this->position = -1;

        $this->createdAt = $this->updatedAt = new DateTimeImmutable();

        $this->children = new ArrayCollection();
        $this->translations = new ArrayCollection();
        $this->blockItems = new ArrayCollection();
    }

    public function __toString(): string
    {
        return $this->defaultTranslation->getTitle();
    }

    /**
     * @param list<string> $locales
     */
    public function change(?string $colorHex, array $locales, ?PageInterface $parent): void
    {
        $this->updatedAt = new DateTimeImmutable();
        $this->color = new Color($colorHex);
        $this->translationLocales = $locales;
        $this->parent = $parent;
        $this->setRootParent($parent);
    }

    public function changeColor(?string $colorHex): void
    {
        $this->color = new Color($colorHex);
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

    private function setRootParent(?PageInterface $parent): void
    {
        if ($parent === null) {
            $this->rootParent = $this;
        } else {
            $this->rootParent = $parent->getRootPage();
        }
    }

    public function changeParent(?PageInterface $parent): void
    {
        $this->parent = $parent;
        $this->setRootParent($parent);
    }

    public function setDefaultTranslation(ContentTranslationInterface $trans): void
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

    /**
     * @return array<string, string>
     */
    public function getExistingTranslationLocales(): array
    {
        return $this->translations->map(fn (ContentTranslationInterface $trans) => $trans->getLocale())->toArray();
    }

    public function getDefaultTranslation(): ContentTranslationInterface
    {
        return $this->defaultTranslation;
    }

    public function getRootPage(): PageInterface
    {
        return $this->rootParent;
    }

    public function getParent(): ?PageInterface
    {
        return $this->parent;
    }

    /**
     * @phpstan-assert-if-true null $this->parent
     * @phpstan-assert-if-false PageInterface $this->parent
     */
    public function isRoot(): bool
    {
        return $this->parent === null;
    }

    /**
     * @return Collection<int, PageInterface>
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
     * @return Collection<string, ContentTranslationInterface>
     */
    public function getTranslations(): Collection
    {
        return $this->translations;
    }

    public function canBeDeleted(): bool
    {
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

        $this->parent = null;

        return true;
    }

    public function getRootParent(): PageInterface
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

    public function getFeaturedImage(): ?MediaInterface
    {
        return $this->featuredImage;
    }

    public function changeFeaturedImage(?MediaInterface $image): void
    {
        $this->featuredImage = $image;
    }

    public function hasFeaturedImage(): bool
    {
        return $this->featuredImage !== null;
    }
}
