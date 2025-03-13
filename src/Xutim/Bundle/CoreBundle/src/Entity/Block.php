<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Entity;

use DateTimeImmutable;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ReadableCollection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Embedded;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\OneToMany;
use Doctrine\ORM\Mapping\OrderBy;
use Symfony\Component\Uid\Uuid;
use Xutim\CoreBundle\Repository\BlockRepository;

#[Entity(repositoryClass: BlockRepository::class)]
class Block
{
    use TimestampableTrait;

    #[Id]
    #[Column(type: 'uuid')]
    private Uuid $id;

    #[Column(type: Types::STRING, length: 255, unique: true)]
    private string $code;

    #[Column(type: Types::STRING, length: 255)]
    private string $name;

    #[Column(type: Types::TEXT)]
    private string $description;

    #[Column(type: Types::STRING, length: 255)]
    private string $layout;

    #[Embedded(class: Color::class)]
    private Color $color;

    /** @var Collection<int, BlockItem> */
    #[OneToMany(mappedBy: 'block', targetEntity: BlockItem::class)]
    #[OrderBy(['position' => 'ASC'])]
    private Collection $blockItems;

    public function __construct(
        string $code,
        string $name,
        string $description,
        ?string $colorHex,
        string $layout
    ) {
        $this->id = Uuid::v4();
        $this->createdAt = $this->updatedAt = new DateTimeImmutable();
        $this->code = $code;
        $this->name = $name;
        $this->description = $description;
        $this->layout = $layout;
        $this->color = new Color($colorHex);
        $this->blockItems = new ArrayCollection();
    }

    public function change(string $code, string $name, string $description, string $layout): void
    {
        $this->code = $code;
        $this->name = $name;
        $this->description = $description;
        $this->layout = $layout;
    }

    public function getId(): Uuid
    {
        return $this->id;
    }

    public function getCode(): string
    {
        return $this->code;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function getLayout(): string
    {
        return $this->layout;
    }

    public function simpleItemRandomizedByWeek(): ?BlockItem
    {
        $simpleItems = $this->getSimpleItems();
        if ($simpleItems->isEmpty()) {
            return null;
        }

        $weekNumber = date('W');
        $index = $weekNumber % $simpleItems->count();

        return $simpleItems->get($index);
    }

    public function getColor(): Color
    {
        return $this->color;
    }

    /**
     * @return ReadableCollection<int, BlockItem>
     */
    public function getObjectBlockItemsByLocale(string $locale): ReadableCollection
    {
        return $this->blockItems->filter(function (BlockItem $item) use ($locale) {
            if ($item->hasPage() === false && $item->hasArticle() === false) {
                return false;
            }
            /** @var Page|Article $object */
            $object = $item->getObject();

            if ($object->getTranslationByLocale($locale) === null) {
                return false;
            }

            return true;
        });
    }

    /**
     * @return Collection<int, BlockItem>
     */
    public function getBlockItems(): Collection
    {
        return $this->blockItems;
    }

    /**
     * @return ReadableCollection<int, BlockItem>
     */
    public function getPagesItems(): ReadableCollection
    {
        return $this->blockItems->filter(fn (BlockItem $item) => $item->hasPage());
    }

    /**
     * @return ReadableCollection<int, BlockItem>
     */
    public function getArticlesItems(): ReadableCollection
    {
        return $this->blockItems->filter(fn (BlockItem $item) => $item->hasArticle());
    }

    /**
     * @return ReadableCollection<int, BlockItem>
     */
    public function getSimpleItems(): ReadableCollection
    {
        return $this->blockItems->filter(fn (BlockItem $item) => $item->isSimpleItem());
    }

    public function addItem(BlockItem $item): void
    {
        $this->blockItems->add($item);
    }
}
