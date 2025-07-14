<?php

declare(strict_types=1);

namespace Xutim\SecurityBundle\Factory;

use Symfony\Component\Uid\Uuid;
use Xutim\SecurityBundle\Domain\Factory\UserFactoryInterface;
use Xutim\SecurityBundle\Domain\Model\UserInterface;

class UserFactory implements UserFactoryInterface
{
    public function __construct(private readonly string $entityClass)
    {
        if (!class_exists($entityClass)) {
            throw new \InvalidArgumentException(sprintf('User class "%s" does not exist.', $entityClass));
        }
    }

    /**
     * @param array<string> $roles
     * @param list<string>  $locales
     */
    public function create(
        Uuid $id,
        string $email,
        string $name,
        string $password,
        array $roles,
        array $locales,
        string $avatar
    ): UserInterface {
        /** @var UserInterface $user */
        $user = new ($this->entityClass)($id, $email, $name, $password, $roles, $locales, $avatar);

        return $user;
    }
}
