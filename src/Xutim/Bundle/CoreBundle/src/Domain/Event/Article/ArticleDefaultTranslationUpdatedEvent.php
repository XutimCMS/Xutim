<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Domain\Event\Article;

use DateTimeImmutable;
use Symfony\Component\Uid\Uuid;
use Xutim\Domain\DomainEvent;

final readonly class ArticleDefaultTranslationUpdatedEvent implements DomainEvent
{
    public DateTimeImmutable $createdAt;

    public function __construct(public Uuid $id, public Uuid $translationId)
    {
        $this->createdAt = new DateTimeImmutable();
    }
}
