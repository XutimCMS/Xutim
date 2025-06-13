<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Domain\Event\Article;

use DateTimeImmutable;
use Symfony\Component\Uid\Uuid;
use Xutim\CoreBundle\Domain\DomainEvent;

final readonly class ArticleCreatedEvent implements DomainEvent
{
    /**
     * @param EditorBlock $content
    */
    public function __construct(
        public Uuid $id,
        public Uuid $defaultTranslation,
        public string $preTitle,
        public string $title,
        public string $subTitle,
        public string $slug,
        public array $content,
        public string $description,
        public string $defaultLanguage,
        public DateTimeImmutable $createdAt,
        public ?string $layout,
        public ?Uuid $featuredImageId
    ) {
    }
}
