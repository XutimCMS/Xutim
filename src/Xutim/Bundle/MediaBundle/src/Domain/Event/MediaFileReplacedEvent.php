<?php

declare(strict_types=1);

namespace Xutim\MediaBundle\Domain\Event;

use Symfony\Component\Uid\Uuid;
use Xutim\Domain\DomainEvent;

final readonly class MediaFileReplacedEvent implements DomainEvent
{
    public function __construct(
        public Uuid $mediaId,
        public string $newMime,
        public int $newSizeBytes,
    ) {
    }
}
