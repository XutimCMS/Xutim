<?php

declare(strict_types=1);

namespace Xutim\MediaBundle\Message;

use Symfony\Component\Uid\Uuid;

final readonly class RegenerateVariantsMessage
{
    public function __construct(
        public Uuid $mediaId,
    ) {
    }
}
