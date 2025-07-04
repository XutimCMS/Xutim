<?php

declare(strict_types=1);

namespace Xutim\SecurityBundle\Factory;

use Xutim\SecurityBundle\Security\UserInterface;
use Xutim\SecurityComponent\Domain\Model\ResetPasswordRequestInterface;

class ResetPasswordRequestFactory
{
    public function __construct(private readonly string $entityClass)
    {
        if (!class_exists($entityClass)) {
            throw new \InvalidArgumentException(sprintf('ResetPasswordRequest class "%s" does not exist.', $entityClass));
        }
    }

    public function create(
        UserInterface $user,
        \DateTimeInterface $expiresAt,
        string $selector,
        string $hashedToken
    ): ResetPasswordRequestInterface {
        /** @var ResetPasswordRequestInterface $item */
        $item = new ($this->entityClass)(
            $user,
            $expiresAt,
            $selector,
            $hashedToken
        );

        return $item;
    }
}
