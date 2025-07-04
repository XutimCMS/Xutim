<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Domain\Event\ContentTranslation;

use DateTimeImmutable;
use Symfony\Component\Uid\Uuid;
use Xutim\Domain\DomainEvent;

final readonly class ContentTranslationCreatedEvent implements DomainEvent
{
    /**
     * @param EditorBlock $content
    */
    public function __construct(
        public Uuid $id,
        public string $preTitle,
        public string $title,
        public string $subTitle,
        public string $slug,
        public array $content,
        public string $description,
        public string $language,
        public DateTimeImmutable $createdAt,
        public ?Uuid $pageId,
        public ?Uuid $articleId,
    ) {
    }
}
