<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Domain\Event\ContentTranslation;

use DateTimeImmutable;
use Symfony\Component\Uid\Uuid;
use Xutim\CoreBundle\Domain\DomainEvent;
use Xutim\CoreBundle\Entity\ContentTranslation;

class ContentTranslationUpdatedEvent implements DomainEvent
{
    /**
     * @param array{}|array{time: int, blocks: array{}|array{id: string, type: string, data: array<string, mixed>}, version: string} $content
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
        public DateTimeImmutable $createdAt
    ) {
    }

    public static function fromContentTranslation(ContentTranslation $trans): self
    {
        return new ContentTranslationUpdatedEvent(
            $trans->getId(),
            $trans->getPreTitle(),
            $trans->getTitle(),
            $trans->getSubTitle(),
            $trans->getSlug(),
            $trans->getContent(),
            $trans->getDescription(),
            $trans->getLocale(),
            $trans->getUpdatedAt()
        );
    }
}
