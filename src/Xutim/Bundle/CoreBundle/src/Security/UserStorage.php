<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Security;

use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Xutim\CoreBundle\Domain\Model\UserInterface;

/**
 * @author Tomas Jakl <tomasjakll@gmail.com>
 */
class UserStorage
{
    public function __construct(private readonly TokenStorageInterface $tokenStorage)
    {
    }

    public function getUser(): ?UserInterface
    {
        $user = $this->tokenStorage->getToken()?->getUser();
        if ($user === null) {
            return null;
        }

        /** @var UserInterface $user */
        return $user;
    }

    public function getUserWithException(): UserInterface
    {
        $user = $this->tokenStorage->getToken()?->getUser();
        if ($user === null) {
            throw new NotFoundHttpException('User is not authenticated.');
        }

        /** @var UserInterface $user */
        return $user;
    }
}
