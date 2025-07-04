<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Domain\Event\Page;

use Symfony\Component\Uid\Uuid;
use Xutim\Domain\DomainEvent;

readonly class PageFileUploadedEvent implements DomainEvent
{
    public function __construct(
        public Uuid $id,
        public string $dataPath,
        public string $name,
        public Uuid $pageId
    ) {
    }
}
