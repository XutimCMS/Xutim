<?php

declare(strict_types=1);

namespace Xutim\SecurityBundle\Action\Security;

class LogoutAction
{
    public function __invoke(): void
    {
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }
}
