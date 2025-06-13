<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Domain\Event\Page;

use Symfony\Component\Uid\Uuid;
use Xutim\CoreBundle\Domain\DomainEvent;

final readonly class PageFeaturedImageUpdatedEvent implements DomainEvent
{
    public function __construct(public Uuid $id, public ?Uuid $featuredImageId)
    {
    }
}
