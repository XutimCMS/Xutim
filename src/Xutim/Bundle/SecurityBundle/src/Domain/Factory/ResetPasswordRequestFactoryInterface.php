<?php

declare(strict_types=1);

namespace Xutim\SecurityBundle\Domain\Factory;

use Xutim\SecurityBundle\Domain\Model\ResetPasswordRequestInterface;
use Xutim\SecurityBundle\Domain\Model\UserInterface;

interface ResetPasswordRequestFactoryInterface
{
    public function create(
        UserInterface $user,
        \DateTimeInterface $expiresAt,
        string $selector,
        string $hashedToken
    ): ResetPasswordRequestInterface;
}
