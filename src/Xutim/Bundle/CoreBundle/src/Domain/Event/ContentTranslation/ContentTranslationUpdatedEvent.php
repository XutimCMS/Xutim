<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Domain\Event\ContentTranslation;

use DateTimeImmutable;
use Symfony\Component\Uid\Uuid;
use Xutim\CoreBundle\Domain\DomainEvent;
use Xutim\CoreBundle\Domain\Model\ContentTranslationInterface;

class ContentTranslationUpdatedEvent implements DomainEvent
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
        public DateTimeImmutable $createdAt
    ) {
    }

    public static function fromContentTranslation(ContentTranslationInterface $trans): self
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
