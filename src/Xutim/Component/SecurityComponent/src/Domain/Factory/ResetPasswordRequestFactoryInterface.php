<?php

declare(strict_types=1);

namespace Xutim\SecurityComponent\Domain\Factory;

use Xutim\SecurityComponent\Domain\Model\ResetPasswordRequestInterface;
use Xutim\SecurityComponent\Domain\Model\UserInterface;

interface ResetPasswordRequestFactoryInterface
{
    public function create(
        UserInterface $user,
        \DateTimeInterface $expiresAt,
        string $selector,
        string $hashedToken
    ): ResetPasswordRequestInterface;
}
