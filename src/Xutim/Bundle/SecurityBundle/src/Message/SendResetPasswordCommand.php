<?php

declare(strict_types=1);

namespace Xutim\SecurityBundle\Message;

use Symfony\Component\Uid\Uuid;

final readonly class SendResetPasswordCommand
{
    public function __construct(public Uuid $id)
    {
    }
}
