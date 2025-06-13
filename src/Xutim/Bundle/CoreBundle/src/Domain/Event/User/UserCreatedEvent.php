<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Domain\Event\User;

use DateTimeImmutable;
use Symfony\Component\Uid\Uuid;
use Xutim\CoreBundle\Domain\DomainEvent;

class UserCreatedEvent implements DomainEvent
{
    public DateTimeImmutable $createdAt;

    /**
     * @param array<string> $roles
     * @param array<string> $transLocales
     */
    public function __construct(
        public Uuid $id,
        public string $email,
        public string $password,
        public array $roles,
        public array $transLocales,
        public string $avatar
    ) {
        $this->createdAt = new DateTimeImmutable();
    }
}
