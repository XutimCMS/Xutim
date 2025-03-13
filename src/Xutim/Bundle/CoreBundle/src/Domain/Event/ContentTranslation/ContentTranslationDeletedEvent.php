<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Domain\Event\ContentTranslation;

use DateTimeImmutable;
use Symfony\Component\Uid\Uuid;
use Xutim\CoreBundle\Domain\DomainEvent;

class ContentTranslationDeletedEvent implements DomainEvent
{
    public DateTimeImmutable $createdAt;

    public function __construct(public Uuid $id)
    {
        $this->createdAt = new DateTimeImmutable();
    }
}
