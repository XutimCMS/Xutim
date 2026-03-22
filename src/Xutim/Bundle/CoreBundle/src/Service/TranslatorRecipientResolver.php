<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Service;

use Xutim\SecurityBundle\Domain\Model\UserInterface;
use Xutim\SecurityBundle\Repository\UserRepositoryInterface;

final readonly class TranslatorRecipientResolver
{
    public function __construct(private UserRepositoryInterface $userRepository)
    {
    }

    /**
     * @param list<string> $locales
     *
     * @return list<UserInterface>
     */
    public function resolveForLocales(array $locales, ?string $excludeUserIdentifier = null): array
    {
        $users = $this->userRepository->findAll();
        $recipients = [];

        foreach ($users as $user) {
            if (!$user->isTranslator()) {
                continue;
            }
            if ($user->getTranslationLocales() === []) {
                continue;
            }
            if ($excludeUserIdentifier !== null && $user->getUserIdentifier() === $excludeUserIdentifier) {
                continue;
            }

            $userLocales = $user->getTranslationLocales();
            foreach ($locales as $locale) {
                if (in_array($locale, $userLocales, true)) {
                    $recipients[$user->getId()->toRfc4122()] = $user;
                    break;
                }
            }
        }

        return array_values($recipients);
    }
}
