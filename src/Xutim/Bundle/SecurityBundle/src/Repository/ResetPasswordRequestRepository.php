<?php

declare(strict_types=1);

namespace Xutim\SecurityBundle\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use SymfonyCasts\Bundle\ResetPassword\Model\ResetPasswordRequestInterface as ModelResetPasswordRequestInterface;
use SymfonyCasts\Bundle\ResetPassword\Persistence\Repository\ResetPasswordRequestRepositoryTrait;
use SymfonyCasts\Bundle\ResetPassword\Persistence\ResetPasswordRequestRepositoryInterface;
use Xutim\CoreBundle\Exception\LogicException;
use Xutim\SecurityBundle\Factory\ResetPasswordRequestFactory;
use Xutim\SecurityBundle\Security\ResetPasswordRequest;
use Xutim\SecurityBundle\Security\User;
use Xutim\SecurityComponent\Domain\Model\ResetPasswordRequestInterface;

/**
 * @extends ServiceEntityRepository<ResetPasswordRequest>
 */
class ResetPasswordRequestRepository extends ServiceEntityRepository implements ResetPasswordRequestRepositoryInterface
{
    use ResetPasswordRequestRepositoryTrait;

    public function __construct(
        ManagerRegistry $registry,
        string $entityClass,
        private readonly ResetPasswordRequestFactory $factory
    ) {
        parent::__construct($registry, $entityClass);
    }

    public function createResetPasswordRequest(object $user, \DateTimeInterface $expiresAt, string $selector, string $hashedToken): ModelResetPasswordRequestInterface
    {
        if (!($user instanceof User)) {
            throw new LogicException('User must be of type Xutim\\SecurityBundle\\Security\\User');
        }

        /** @var ResetPasswordRequestInterface $req */
        $req = $this->factory->create($user, $expiresAt, $selector, $hashedToken);

        return $req;
    }
}
