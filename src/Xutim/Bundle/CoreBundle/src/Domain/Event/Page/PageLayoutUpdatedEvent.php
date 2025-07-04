<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Domain\Event\Page;

use DateTimeImmutable;
use Symfony\Component\Uid\Uuid;
use Xutim\Domain\DomainEvent;

final readonly class PageLayoutUpdatedEvent implements DomainEvent
{
    public DateTimeImmutable $createdAt;

    public function __construct(public Uuid $id, public ?string $layout)
    {
        $this->createdAt = new DateTimeImmutable();
    }
}
