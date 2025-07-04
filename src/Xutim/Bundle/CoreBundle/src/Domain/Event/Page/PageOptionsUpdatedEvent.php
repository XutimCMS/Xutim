<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Domain\Event\Page;

use DateTimeImmutable;
use Symfony\Component\Uid\Uuid;
use Xutim\CoreBundle\Entity\PublicationStatus;
use Xutim\Domain\DomainEvent;

class PageOptionsUpdatedEvent implements DomainEvent
{
    public function __construct(
        public Uuid $id,
        public PublicationStatus $status,
        public ?DateTimeImmutable $publishedAt
    ) {
    }
}
