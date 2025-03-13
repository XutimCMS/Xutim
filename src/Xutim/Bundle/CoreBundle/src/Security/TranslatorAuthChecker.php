<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Security;

use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Xutim\CoreBundle\Entity\User;

/**
 * @author Tomas Jakl <tomasjakll@gmail.com>
 */
class TranslatorAuthChecker
{
    public function __construct(
        private readonly UserStorage $userStorage,
        private readonly Security $security
    ) {
    }

    public function denyUnlessCanTranslate(string $locale): void
    {
        if ($this->canTranslate($locale) === true) {
            return;
        }

        throw new AccessDeniedException('The user cannot translate "' . $locale . '" locale.');
    }

    public function canTranslate(string $locale): bool
    {
        if ($this->security->isGranted(User::ROLE_EDITOR)) {
            return true;
        }
        if ($this->security->isGranted(User::ROLE_TRANSLATOR) === false) {
            return false;
        }
        $user = $this->userStorage->getUserWithException();
        if (in_array($locale, $user->getTranslationLocales(), true) === false) {
            return false;
        }

        return true;
    }
}
