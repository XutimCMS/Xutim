<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Domain\Event\File;

use DateTimeImmutable;
use Symfony\Component\Uid\Uuid;
use Xutim\CoreBundle\Domain\DomainEvent;

class FileCopyrightUpdatedEvent implements DomainEvent
{
    public DateTimeImmutable $createdAt;

    public function __construct(public Uuid $id, public string $copyright)
    {
        $this->createdAt = new DateTimeImmutable();
    }
}
