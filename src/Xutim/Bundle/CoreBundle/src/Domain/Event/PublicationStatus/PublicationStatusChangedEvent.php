<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Domain\Event\PublicationStatus;

use Symfony\Component\Uid\Uuid;
use Xutim\CoreBundle\Domain\DomainEvent;
use Xutim\CoreBundle\Entity\PublicationStatus;

final readonly class PublicationStatusChangedEvent implements DomainEvent
{
    public function __construct(
        public Uuid $objectId,
        public string $object,
        public PublicationStatus $status
    ) {
    }
}
