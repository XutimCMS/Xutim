<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\PasswordUpgraderInterface;
use Xutim\CoreBundle\Dto\Admin\FilterDto;
use Xutim\CoreBundle\Entity\User;

/**
 * @extends ServiceEntityRepository<User>
 * @implements PasswordUpgraderInterface<User>
 */
class UserRepository extends ServiceEntityRepository implements PasswordUpgraderInterface
{
    public const FILTER_ORDER_COLUMN_MAP = [
        'id' => 'user.id',
        'name' => 'user.name',
        'email' => 'user.email'
    ];

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }

    /**
     * @return array<string, string>
     */
    public function findAllUsernamesByEmail(): array
    {
        /** @var array<string, string> $emails */
        $emails = $this->createQueryBuilder('user')
            ->select('user.name', 'user.email')
            ->indexBy('user', 'user.email')
            ->getQuery()
            ->getArrayResult()
        ;

        return $emails;
    }

    public function save(User $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(User $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * Used to upgrade (rehash) the user's password automatically over time.
     */
    public function upgradePassword(
        PasswordAuthenticatedUserInterface $user,
        string $newHashedPassword
    ): void {
        $user->changePassword($newHashedPassword);

        $this->save($user, true);
    }

    public function queryByFilter(FilterDto $filter): QueryBuilder
    {
        $builder = $this->createQueryBuilder('user');
        if ($filter->hasSearchTerm() === true) {
            $builder
                ->where($builder->expr()->like('LOWER(user.email)', ':searchTerm'))
                ->orWhere($builder->expr()->like('LOWER(user.name)', ':searchTerm'))
                ->setParameter('searchTerm', '%' . strtolower($filter->searchTerm) . '%');
        }

        // Check if the order has a valid orderDir and orderColumn parameters.
        if (in_array(
            $filter->orderColumn,
            array_keys(self::FILTER_ORDER_COLUMN_MAP),
            true
        ) === true) {
            $builder->orderBy(
                self::FILTER_ORDER_COLUMN_MAP[$filter->orderColumn],
                $filter->getOrderDir()
            );
        } else {
            $builder->orderBy('user.id', 'asc');
        }

        return $builder;
    }

    public function isEmailUsed(string $email): bool
    {
        $existingUser = $this->findOneBy(['email' => $email]);

        return $existingUser !== null;
    }

    public function isNameUsed(string $name): bool
    {
        $existingUser = $this->findOneBy(['name' => $name]);

        return $existingUser !== null;
    }
}
