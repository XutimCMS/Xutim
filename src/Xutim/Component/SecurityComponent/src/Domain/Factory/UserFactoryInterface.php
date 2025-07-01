<?php

declare(strict_types=1);

namespace Xutim\SecurityComponent\Domain\Factory;

use Symfony\Component\Uid\Uuid;
use Xutim\SecurityComponent\Domain\Model\UserInterface;

interface UserFactoryInterface
{
    /**
     * @param list<string> $roles
     * @param list<string> $locales
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
