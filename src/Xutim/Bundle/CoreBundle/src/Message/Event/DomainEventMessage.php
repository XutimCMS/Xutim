<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Message\Event;

use Symfony\Component\Uid\Uuid;
use Xutim\Domain\DomainEvent;

final readonly class DomainEventMessage
{
    public function __construct(
        public Uuid $objectId,
        public string $className,
        public DomainEvent $event,
        public string $userIdentifier
    ) {
    }
}
