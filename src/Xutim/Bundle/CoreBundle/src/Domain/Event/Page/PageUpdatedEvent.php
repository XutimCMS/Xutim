<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Domain\Event\Page;

use DateTimeImmutable;
use Symfony\Component\Uid\Uuid;
use Xutim\CoreBundle\Domain\DomainEvent;

readonly class PageUpdatedEvent implements DomainEvent
{
    /**
     * @param array<int, string>  $locales
     */
    public function __construct(
        public Uuid $id,
        public ?string $color,
        public array $locales,
        public DateTimeImmutable $createdAt,
        public ?Uuid $parentId
    ) {
    }
}
