<?php

declare(strict_types=1);

namespace Xutim\SecurityBundle\Message;

use Symfony\Component\Uid\Uuid;

final readonly class ChangePasswordCommand
{
    public function __construct(
        public Uuid $id,
        public string $encodedPassword
    ) {
    }
}
