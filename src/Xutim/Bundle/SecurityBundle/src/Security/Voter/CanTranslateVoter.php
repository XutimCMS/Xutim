<?php

declare(strict_types=1);

namespace Xutim\SecurityBundle\Security\Voter;

use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\AccessDecisionManagerInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Xutim\SecurityBundle\Domain\Model\UserInterface;

/**
 * @extends Voter<string, string>
 */
class CanTranslateVoter extends Voter
{
    public const TRANSLATE = 'can_translate';

    public function __construct(
        private AccessDecisionManagerInterface $accessDecisionManager,
    ) {
    }

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

        if ($this->accessDecisionManager->decide($token, ['ROLE_EDITOR'])) {
            return true;
        }

        if ($this->accessDecisionManager->decide($token, ['ROLE_TRANSLATOR'])) {
            return in_array($locale, $user->getTranslationLocales(), true);
        }

        return false;
    }
}
