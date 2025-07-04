<?php

declare(strict_types=1);

namespace Xutim\SecurityComponent\Domain\Event;

use DateTimeImmutable;
use Symfony\Component\Uid\Uuid;
use Xutim\Domain\DomainEvent;

class UserUpdatedEvent implements DomainEvent
{
    public DateTimeImmutable $createdAt;

    /**
     * @param array<string> $roles
     * @param array<string> $transLocales
     */
    public function __construct(
        public Uuid $id,
        public string $name,
        public array $roles,
        public array $transLocales
    ) {
        $this->createdAt = new DateTimeImmutable();
    }
}
