<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Domain\Event\User;

use DateTimeImmutable;
use Symfony\Component\Uid\Uuid;
use Xutim\CoreBundle\Domain\DomainEvent;

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
