<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Domain\Event\Article;

use DateTimeImmutable;
use Symfony\Component\Uid\Uuid;
use Xutim\CoreBundle\Domain\DomainEvent;

final readonly class ArticlePageUpdatedEvent implements DomainEvent
{
    public DateTimeImmutable $createdAt;

    public function __construct(public Uuid $id, public Uuid $pageId)
    {
        $this->createdAt = new DateTimeImmutable();
    }
}
