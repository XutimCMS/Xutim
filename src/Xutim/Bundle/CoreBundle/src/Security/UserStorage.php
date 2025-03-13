<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Security;

use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Xutim\CoreBundle\Entity\User;

/**
 * @author Tomas Jakl <tomasjakll@gmail.com>
 */
class UserStorage
{
    public function __construct(private readonly TokenStorageInterface $tokenStorage)
    {
    }

    public function getUser(): ?User
    {
        $user = $this->tokenStorage->getToken()?->getUser();
        if ($user === null) {
            return null;
        }

        /** @var User $user */
        return $user;
    }

    public function getUserWithException(): User
    {
        $user = $this->tokenStorage->getToken()?->getUser();
        if ($user === null) {
            throw new NotFoundHttpException('User is not authenticated.');
        }

        /** @var User $user */
        return $user;
    }
}
