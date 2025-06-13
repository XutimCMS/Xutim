<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Domain\Event\Article;

use Symfony\Component\Uid\Uuid;
use Xutim\CoreBundle\Domain\DomainEvent;

final readonly class ArticleFeaturedImageUpdatedEvent implements DomainEvent
{
    public function __construct(public Uuid $id, public ?Uuid $featuredImageId)
    {
    }
}
