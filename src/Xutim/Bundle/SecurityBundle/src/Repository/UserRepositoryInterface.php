<?php

declare(strict_types=1);

namespace Xutim\SecurityBundle\Repository;

use Doctrine\ORM\QueryBuilder;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\PasswordUpgraderInterface;
use Symfony\Component\Uid\Uuid;
use Xutim\CoreBundle\Dto\Admin\FilterDto;
use Xutim\SecurityBundle\Domain\Model\UserInterface;

interface UserRepositoryInterface extends PasswordUpgraderInterface
{
    /**
     * @return array<string, string>
     */
    public function findAllUsernamesByEmail(): array;

    /**
     * Used to upgrade (rehash) the user's password automatically over time.
     */
    public function upgradePassword(
        PasswordAuthenticatedUserInterface $user,
        string $newHashedPassword
    ): void;

    public function queryByFilter(FilterDto $filter): QueryBuilder;

    public function isEmailUsed(string $email): bool;

    public function isNameUsed(string $name): bool;

    public function save(UserInterface $entity, bool $flush = false): void;

    public function remove(UserInterface $entity, bool $flush = false): void;

    public function findById(Uuid|string $id): ?UserInterface;

    public function findOneByEmail(string $email): ?UserInterface;

    public function findOneByName(string $name): ?UserInterface;
}
