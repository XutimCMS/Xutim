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
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\MappedSuperclass;
use Doctrine\ORM\Mapping\OneToMany;
use Doctrine\ORM\Mapping\OrderBy;
use Symfony\Component\Uid\Uuid;
use Xutim\CoreBundle\Domain\Model\ArticleInterface;
use Xutim\CoreBundle\Domain\Model\BlockInterface;
use Xutim\CoreBundle\Domain\Model\BlockItemInterface;
use Xutim\CoreBundle\Domain\Model\PageInterface;

#[MappedSuperclass]
class Block implements BlockInterface
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

    /** @var Collection<int, BlockItemInterface> */
    #[OneToMany(mappedBy: 'block', targetEntity: BlockItemInterface::class)]
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

    public function simpleItemRandomizedByWeek(): ?BlockItemInterface
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
     * @return ReadableCollection<int, BlockItemInterface>
     */
    public function getObjectBlockItemsByLocale(string $locale): ReadableCollection
    {
        return $this->blockItems->filter(function (BlockItemInterface $item) use ($locale) {
            if ($item->hasPage() === false && $item->hasArticle() === false) {
                return false;
            }
            /** @var PageInterface|ArticleInterface $object */
            $object = $item->getObject();

            if ($object->getTranslationByLocale($locale) === null) {
                return false;
            }

            return true;
        });
    }

    /**
     * @return Collection<int, BlockItemInterface>
     */
    public function getBlockItems(): Collection
    {
        return $this->blockItems;
    }

    /**
     * @return ReadableCollection<int, BlockItemInterface>
     */
    public function getPagesItems(): ReadableCollection
    {
        return $this->blockItems->filter(fn (BlockItemInterface $item) => $item->hasPage());
    }

    /**
     * @return ReadableCollection<int, BlockItemInterface>
     */
    public function getArticlesItems(): ReadableCollection
    {
        return $this->blockItems->filter(fn (BlockItemInterface $item) => $item->hasArticle());
    }

    /**
     * @return ReadableCollection<int, BlockItemInterface>
     */
    public function getSimpleItems(): ReadableCollection
    {
        return $this->blockItems->filter(fn (BlockItemInterface $item) => $item->isSimpleItem());
    }

    public function addItem(BlockItemInterface $item): void
    {
        $this->blockItems->add($item);
    }
}
