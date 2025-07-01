<?php

declare(strict_types=1);

namespace Xutim\SecurityComponent\Domain\Repository;

use Xutim\SecurityComponent\Domain\Model\UserInterface;

interface UserRepositoryInterface
{
    public function save(UserInterface $entity, bool $flush = false): void;

    public function remove(UserInterface $entity, bool $flush = false): void;

    public function isEmailUsed(string $email): bool;

    public function isNameUsed(string $name): bool;
}
