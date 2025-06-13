<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Form\Admin\Dto;

use Symfony\Component\Uid\Uuid;

final readonly class ImageDto
{
    public function __construct(
        public ?Uuid $id
    ) {
    }
}
