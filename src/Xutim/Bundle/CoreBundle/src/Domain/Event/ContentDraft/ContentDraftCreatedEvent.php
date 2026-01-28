<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Domain\Event\ContentDraft;

use DateTimeImmutable;
use Symfony\Component\Uid\Uuid;
use Xutim\CoreBundle\Domain\Model\ContentDraftInterface;
use Xutim\Domain\DomainEvent;

readonly class ContentDraftCreatedEvent implements DomainEvent
{
    /**
     * @param EditorBlock $content
     */
    public function __construct(
        public Uuid $id,
        public Uuid $draftId,
        public string $preTitle,
        public string $title,
        public string $subTitle,
        public string $slug,
        public array $content,
        public string $description,
        public DateTimeImmutable $createdAt,
    ) {
    }

    public static function fromDraft(ContentDraftInterface $draft): self
    {
        return new self(
            $draft->getTranslation()->getId(),
            $draft->getId(),
            $draft->getPreTitle(),
            $draft->getTitle(),
            $draft->getSubTitle(),
            $draft->getSlug(),
            $draft->getContent(),
            $draft->getDescription(),
            $draft->getCreatedAt(),
        );
    }
}
