<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Dto\Admin\Page;

use Symfony\Component\Uid\Uuid;

readonly class PagePathDto
{
    public function __construct(public Uuid $id, public string $title)
    {
    }
}
