<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Dto\Admin\Page;

use DateTimeImmutable;
use Xutim\CoreBundle\Entity\PublicationStatus;

class PageOptionsDto
{
    public function __construct(
        public ?DateTimeImmutable $publishedAt,
        public PublicationStatus $status
    ) {
    }
}
