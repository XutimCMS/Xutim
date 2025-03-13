<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Message\Command\User;

use Symfony\Component\Uid\Uuid;

readonly class DeleteUserCommand
{
    public function __construct(public Uuid $id, public string $userIdentifier)
    {
    }
}
