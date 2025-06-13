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
use Doctrine\ORM\Mapping\ManyToMany;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\MappedSuperclass;
use Doctrine\ORM\Mapping\OneToMany;
use Doctrine\ORM\Mapping\OrderBy;
use Symfony\Component\Uid\Uuid;
use Webmozart\Assert\Assert;
use Xutim\CoreBundle\Domain\Model\ArticleInterface;
use Xutim\CoreBundle\Domain\Model\FileInterface;
use Xutim\CoreBundle\Domain\Model\TagInterface;
use Xutim\CoreBundle\Domain\Model\TagTranslationInterface;

#[MappedSuperclass]
class Tag implements TagInterface
{
    use PublicationStatusTrait;
    use TimestampableTrait;
    use ArchiveStatusTrait;
    /** @use BasicTranslatableTrait<TagTranslationInterface> */
    use BasicTranslatableTrait;

    #[Id]
    #[Column(type: 'uuid')]
    private Uuid $id;

    #[ManyToOne(targetEntity: FileInterface::class, inversedBy: 'featuredTags')]
    private ?FileInterface $featuredImage;

    #[Embedded(class: Color::class)]
    private Color $color;

    #[Column(type: Types::STRING, nullable: true)]
    private ?string $layout;

    #[Column(type: Types::BOOLEAN)]
    private bool $excludeFromNews = false;

    /** @var Collection<int, TagTranslationInterface> */
    #[OneToMany(mappedBy: 'tag', targetEntity: TagTranslationInterface::class, indexBy: 'locale')]
    #[OrderBy(['locale' => 'ASC'])]
    private Collection $translations;
    
    /** @var Collection<int, ArticleInterface> */
    #[ManyToMany(targetEntity: ArticleInterface::class, mappedBy: 'tags')]
    private Collection $articles;

    public function __construct(Color $color, ?FileInterface $featuredImage, ?string $layout)
    {
        $this->id = Uuid::v4();
        $this->publishedAt = null;
        $this->status = PublicationStatus::Draft;
        $this->createdAt = $this->updatedAt = new DateTimeImmutable();
        $this->featuredImage = $featuredImage;
        $this->color = $color;
        $this->archived = false;
        $this->layout = $layout;

        $this->translations = new ArrayCollection();
        $this->articles = new ArrayCollection();
    }

    public function __toString(): string
    {
        $translation = $this->translations->first();
        Assert::notFalse($translation);

        return $translation->getName();
    }

    public function change(Color $color, ?FileInterface $image): void
    {
        $this->color = $color;
        $this->featuredImage = $image;
    }

    public function addTranslation(TagTranslationInterface $trans): void
    {
        if ($this->translations->contains($trans) === true) {
            return;
        }

        $this->translations->add($trans);
    }

    public function toggleExcludeFromNews(): void
    {
        $this->excludeFromNews = !$this->excludeFromNews;
    }

    public function getId(): Uuid
    {
        return $this->id;
    }

    public function getFeaturedImage(): ?FileInterface
    {
        return $this->featuredImage;
    }

    public function getColor(): Color
    {
        return $this->color;
    }

    public function getLayout(): ?string
    {
        return $this->layout;
    }

    public function changeLayout(?string $layout): void
    {
        $this->layout = $layout;
    }

    /**
     * @return Collection<int, TagTranslationInterface>
    */
    public function getTranslations(): Collection
    {
        return $this->translations;
    }

    /**
     * @return Collection<int, ArticleInterface>
    */
    public function getArticles(): Collection
    {
        return $this->articles;
    }

    public function isExcludeFromNews(): bool
    {
        return $this->excludeFromNews;
    }
}
