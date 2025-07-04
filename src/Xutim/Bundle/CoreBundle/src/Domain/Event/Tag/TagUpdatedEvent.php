<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Domain\Event\Tag;

use Symfony\Component\Uid\Uuid;
use Xutim\Domain\DomainEvent;

final readonly class TagUpdatedEvent implements DomainEvent
{
    public function __construct(
        public Uuid $id,
        public Uuid $translationId,
        public string $name,
        public string $slug,
        public string $locale,
        public string $color,
        public ?Uuid $featuredImageId
    ) {
    }
}
