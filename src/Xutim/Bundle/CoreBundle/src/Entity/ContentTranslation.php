<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Entity;

use DateTimeImmutable;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\UniqueConstraint;
use Symfony\Component\Uid\Uuid;
use Webmozart\Assert\Assert;
use Xutim\CoreBundle\Repository\ContentTranslationRepository;

#[Entity(repositoryClass: ContentTranslationRepository::class)]
#[UniqueConstraint(columns: ['slug', 'locale'])]
class ContentTranslation
{
    use PublicationStatusTrait;
    use TimestampableTrait;
    use ContentTranslationTrait;

    #[Id]
    #[Column(type: 'uuid')]
    private Uuid $id;

    #[Column(type: 'integer', nullable: true)]
    private ?int $spipId;

    #[Column(type: 'integer', nullable: false)]
    private int $visits;

    #[ManyToOne(targetEntity: Page::class, inversedBy: 'translations')]
    #[JoinColumn(nullable: true)]
    private ?Page $page;

    #[ManyToOne(targetEntity: Article::class, inversedBy: 'translations')]
    #[JoinColumn(nullable: true)]
    private ?Article $article;

    /**
     * @param array{}|array{
     *     time: int,
     *     blocks: array{}|array<array{id: string, type: string, data: array<string, mixed>}>,
     *     version: string
     * } $content
     */
    public function __construct(
        string $preTitle,
        string $title,
        string $subTitle,
        string $slug,
        array $content,
        string $locale,
        string $description,
        ?Page $page,
        ?Article $article,
        ?int $spipId = null
    ) {
        $this->id = Uuid::v4();
        $this->publishedAt = null;
        $this->status = PublicationStatus::Draft;
        $this->createdAt = $this->updatedAt = new DateTimeImmutable();
        $this->hasUntranslatedChange = false;
        $this->preTitle = $preTitle;
        $this->title = $title;
        $this->subTitle = $subTitle;
        $this->slug = $slug;
        $this->content = $content;
        $this->locale = $locale;
        $this->description = $description;
        $this->page = $page;
        $this->article = $article;
        $this->spipId = $spipId;
        $this->visits = 0;
        Assert::false($page === null && $article === null, 'Content translation needs either page or article.');
    }

    public function __toString(): string
    {
        return $this->title;
    }

    public function getId(): Uuid
    {
        return $this->id;
    }

    /**
     * @phpstan-assert-if-true Page $this->page
     * @phpstan-assert-if-true null $this->article
     */
    public function hasPage(): bool
    {
        return $this->page !== null;
    }

    /**
     * @phpstan-assert-if-true Article $this->article
     * @phpstan-assert-if-true null $this->page
     */
    public function hasArticle(): bool
    {
        return $this->article !== null;
    }

    public function getPage(): Page
    {
        Assert::notNull($this->page);
        return $this->page;
    }

    public function getArticle(): Article
    {
        Assert::notNull($this->article);
        return $this->article;
    }

    public function getObject(): Article|Page
    {
        return $this->hasArticle() ? $this->article : $this->page;
    }
}
