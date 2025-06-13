<?php

declare(strict_types=1);

namespace Xutim\EventBundle\Entity;

use DateTimeImmutable;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\MappedSuperclass;
use Doctrine\ORM\Mapping\OneToMany;
use Doctrine\ORM\Mapping\OrderBy;
use Symfony\Component\Uid\Uuid;
use Webmozart\Assert\Assert;
use Xutim\CoreBundle\Domain\Model\ArticleInterface;
use Xutim\CoreBundle\Domain\Model\PageInterface;
use Xutim\CoreBundle\Entity\Article;
use Xutim\CoreBundle\Entity\BasicTranslatableTrait;
use Xutim\CoreBundle\Entity\Page;
use Xutim\CoreBundle\Entity\PublicationStatus;
use Xutim\CoreBundle\Entity\PublicationStatusTrait;
use Xutim\CoreBundle\Entity\TimestampableTrait;
use Xutim\EventBundle\Domain\Model\EventInterface;
use Xutim\EventBundle\Domain\Model\EventTranslationInterface;

#[MappedSuperclass]
class Event implements EventInterface
{
    use TimestampableTrait;
    use PublicationStatusTrait;
    /** @use BasicTranslatableTrait<EventTranslationInterface> */
    use BasicTranslatableTrait;

    #[Id]
    #[Column(type: 'uuid')]
    private Uuid $id;

    #[Column(type: Types::DATETIME_IMMUTABLE)]
    private DateTimeImmutable $startsAt;

    #[Column(type: Types::DATETIME_IMMUTABLE)]
    private DateTimeImmutable $endsAt;

    /** @var Collection<int, EventTranslationInterface> */
    #[OneToMany(mappedBy: 'event', targetEntity: EventTranslationInterface::class, indexBy: 'locale')]
    #[OrderBy(['locale' => 'ASC'])]
    private Collection $translations;

    #[Column(type: Types::DATETIME_IMMUTABLE, nullable: true)]
    private ?DateTimeImmutable $publishedAt;

    #[ManyToOne(targetEntity: PageInterface::class, inversedBy: 'translations')]
    #[JoinColumn(nullable: true)]
    private ?PageInterface $page;

    #[ManyToOne(targetEntity: ArticleInterface::class, inversedBy: 'translations')]
    #[JoinColumn(nullable: true)]
    private ?ArticleInterface $article;

    public function __construct(
        DateTimeImmutable $startsAt,
        DateTimeImmutable $endsAt,
        ?ArticleInterface $article,
        ?PageInterface $page,
    ) {
        $this->id = Uuid::v4();
        $this->createdAt = $this->updatedAt = new DateTimeImmutable();
        $this->status = PublicationStatus::Draft;
        $this->startsAt = $startsAt;
        $this->endsAt = $endsAt;
        $this->publishedAt = null;
        $this->translations = new ArrayCollection();
        $this->article = $article;
        $this->page = $page;
    }

    public function changeDates(
        DateTimeImmutable $startsAt,
        DateTimeImmutable $endsAt
    ): void {
        $this->startsAt = $startsAt;
        $this->endsAt = $endsAt;
        $this->updatedAt = new DateTimeImmutable();
    }

    public function changeArticle(?ArticleInterface $article): void
    {
        $this->article = $article;
        $this->updatedAt = new DateTimeImmutable();
    }

    public function changePage(?PageInterface $page): void
    {
        $this->page = $page;
        $this->updatedAt = new DateTimeImmutable();
    }

    public function addTranslation(EventTranslationInterface $trans): void
    {
        if ($this->translations->contains($trans) === true) {
            return;
        }
        $this->translations->add($trans);
        $this->updatedAt = new DateTimeImmutable();
    }

    public function getId(): Uuid
    {
        return $this->id;
    }

    public function setPublishedAt(?DateTimeImmutable $date): void
    {
        $this->publishedAt = $date;
    }

    public function getStartsAt(): DateTimeImmutable
    {
        return $this->startsAt;
    }

    public function getEndsAt(): DateTimeImmutable
    {
        return $this->endsAt;
    }

    public function getPublishedAt(): ?DateTimeImmutable
    {
        return $this->publishedAt;
    }

    /**
     * @phpstan-assert-if-true PageInterface $this->page
     * @phpstan-assert-if-true null $this->article
     */
    public function hasPage(): bool
    {
        return $this->page !== null;
    }

    /**
     * @phpstan-assert-if-true ArticleInterface $this->article
     * @phpstan-assert-if-true null $this->page
     */
    public function hasArticle(): bool
    {
        return $this->article !== null;
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
     * @return Collection<int, EventTranslationInterface>
    */
    public function getTranslations(): Collection
    {
        return $this->translations;
    }
}
