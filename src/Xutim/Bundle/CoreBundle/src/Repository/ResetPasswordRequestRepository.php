<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use SymfonyCasts\Bundle\ResetPassword\Persistence\Repository\ResetPasswordRequestRepositoryTrait;
use SymfonyCasts\Bundle\ResetPassword\Persistence\ResetPasswordRequestRepositoryInterface;
use Xutim\CoreBundle\Domain\Factory\ResetPasswordRequestFactory;
use Xutim\CoreBundle\Domain\Model\ResetPasswordRequestInterface as XutimResetPasswordRequestInterface;
use Xutim\CoreBundle\Entity\ResetPasswordRequest;
use Xutim\CoreBundle\Entity\User;
use Xutim\CoreBundle\Exception\LogicException;

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

    public function createResetPasswordRequest(object $user, \DateTimeInterface $expiresAt, string $selector, string $hashedToken): XutimResetPasswordRequestInterface
    {
        if (!($user instanceof User)) {
            throw new LogicException('User must be of type Xutim\CoreBundle\\Entity\\User');
        }

        /** @var XutimResetPasswordRequestInterface $req */
        $req = $this->factory->create($user, $expiresAt, $selector, $hashedToken);

        return $req;
    }
}
