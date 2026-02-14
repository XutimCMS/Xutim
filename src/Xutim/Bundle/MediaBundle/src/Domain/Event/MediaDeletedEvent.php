<?php

declare(strict_types=1);

namespace Xutim\MediaBundle\Domain\Event;

use Symfony\Component\Uid\Uuid;
use Xutim\Domain\DomainEvent;

final readonly class MediaDeletedEvent implements DomainEvent
{
    public function __construct(
        public Uuid $mediaId,
        public string $originalPath,
    ) {
    }
}
