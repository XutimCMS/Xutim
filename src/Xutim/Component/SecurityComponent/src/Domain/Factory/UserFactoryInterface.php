<?php

declare(strict_types=1);

namespace Xutim\SecurityComponent\Domain\Factory;

use Symfony\Component\Uid\Uuid;
use Xutim\SecurityBundle\Security\UserInterface;

interface UserFactoryInterface
{
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
    ): UserInterface;
}
