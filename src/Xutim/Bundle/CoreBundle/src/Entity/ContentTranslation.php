<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Entity;

use DateTimeImmutable;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\MappedSuperclass;
use Doctrine\ORM\Mapping\UniqueConstraint;
use Symfony\Component\Uid\Uuid;
use Webmozart\Assert\Assert;
use Xutim\CoreBundle\Domain\Model\ArticleInterface;
use Xutim\CoreBundle\Domain\Model\ContentTranslationInterface;
use Xutim\CoreBundle\Domain\Model\PageInterface;

#[MappedSuperclass]
#[UniqueConstraint(columns: ['slug', 'locale'])]
class ContentTranslation implements ContentTranslationInterface
{
    use PublicationStatusTrait;
    use TimestampableTrait;
    use ContentTranslationTrait;

    #[Id]
    #[Column(type: 'uuid')]
    private Uuid $id;

    #[Column(type: 'integer', nullable: false)]
    private int $visits;

    #[ManyToOne(targetEntity: PageInterface::class, inversedBy: 'translations')]
    #[JoinColumn(nullable: true)]
    private ?PageInterface $page;

    #[ManyToOne(targetEntity: ArticleInterface::class, inversedBy: 'translations')]
    #[JoinColumn(nullable: true)]
    private ?ArticleInterface $article;

    /**
     * @param EditorBlock $content
     */
    public function __construct(
        string $preTitle,
        string $title,
        string $subTitle,
        string $slug,
        array $content,
        string $locale,
        string $description,
        ?PageInterface $page,
        ?ArticleInterface $article
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

    public function getPage(): PageInterface
    {
        Assert::notNull($this->page);
        return $this->page;
    }

    public function getArticle(): ArticleInterface
    {
        Assert::notNull($this->article);
        return $this->article;
    }

    public function getObject(): ArticleInterface|PageInterface
    {
        return $this->hasArticle() ? $this->article : $this->page;
    }
}
