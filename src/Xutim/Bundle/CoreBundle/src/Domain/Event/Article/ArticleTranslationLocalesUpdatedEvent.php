<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Domain\Event\Article;

use DateTimeImmutable;
use Symfony\Component\Uid\Uuid;
use Xutim\Domain\DomainEvent;

readonly class ArticleTranslationLocalesUpdatedEvent implements DomainEvent
{
    /** @param list<string> $translationLocales */
    public function __construct(
        public Uuid $id,
        public array $translationLocales,
        public DateTimeImmutable $updatedAt,
    ) {
    }
}
