<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Domain\Event\ContentDraft;

use DateTimeImmutable;
use Symfony\Component\Uid\Uuid;
use Xutim\Domain\DomainEvent;

readonly class ContentDraftDiscardedEvent implements DomainEvent
{
    public DateTimeImmutable $createdAt;

    public function __construct(
        public Uuid $id,
        public Uuid $draftId,
    ) {
        $this->createdAt = new DateTimeImmutable();
    }
}
