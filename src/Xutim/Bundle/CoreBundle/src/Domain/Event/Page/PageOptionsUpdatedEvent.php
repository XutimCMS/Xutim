<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Domain\Event\Page;

use DateTimeImmutable;
use Symfony\Component\Uid\Uuid;
use Xutim\CoreBundle\Domain\DomainEvent;
use Xutim\CoreBundle\Entity\PublicationStatus;

class PageOptionsUpdatedEvent implements DomainEvent
{
    public function __construct(
        public Uuid $id,
        public PublicationStatus $status,
        public ?DateTimeImmutable $publishedAt
    ) {
    }
}
