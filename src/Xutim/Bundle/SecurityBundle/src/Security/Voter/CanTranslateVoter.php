<?php

declare(strict_types=1);

namespace Xutim\SecurityBundle\Security\Voter;

use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Xutim\SecurityBundle\Domain\Model\UserInterface;
use Xutim\SecurityBundle\Security\UserRoles;

class CanTranslateVoter extends Voter
{
    public const TRANSLATE = 'can_translate';

    protected function supports(string $attribute, mixed $subject): bool
    {
        return $attribute === self::TRANSLATE && is_string($subject);
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();

        if (!$user instanceof UserInterface) {
            return false;
        }

        /** @var string $locale */
        $locale = $subject;

        // Editors can translate to any language
        if (in_array(UserRoles::ROLE_EDITOR, $user->getRoles(), true)) {
            return true;
        }

        // Regular users need to have the language in their translationLocales
        if (in_array(UserRoles::ROLE_USER, $user->getRoles(), true)) {
            return in_array($locale, $user->getTranslationLocales(), true);
        }

        return false;
    }
}
