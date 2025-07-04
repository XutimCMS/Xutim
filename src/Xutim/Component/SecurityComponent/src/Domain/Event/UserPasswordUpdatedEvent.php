<?php

declare(strict_types=1);

namespace Xutim\SecurityComponent\Domain\Event;

use DateTimeImmutable;
use Symfony\Component\Uid\Uuid;
use Xutim\Domain\DomainEvent;

class UserPasswordUpdatedEvent implements DomainEvent
{
    public DateTimeImmutable $createdAt;

    public function __construct(
        public Uuid $id,
        public string $password
    ) {
        $this->createdAt = new DateTimeImmutable();
    }
}
