<?php

declare(strict_types=1);

namespace Xutim\SecurityComponent\Domain\Event;

use DateTimeImmutable;
use Symfony\Component\Uid\Uuid;
use Xutim\Domain\DomainEvent;

class UserDeletedEvent implements DomainEvent
{
    public DateTimeImmutable $createdAt;

    public function __construct(public Uuid $id)
    {
        $this->createdAt = new DateTimeImmutable();
    }
}
