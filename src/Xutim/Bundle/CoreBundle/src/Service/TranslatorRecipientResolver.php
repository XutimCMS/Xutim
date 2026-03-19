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
            if (!$user instanceof UserInterface) {
                continue;
            }
            if (!$user->isTranslator()) {
                continue;
            }
            if ($excludeUserIdentifier !== null && $user->getUserIdentifier() === $excludeUserIdentifier) {
                continue;
            }

            foreach ($locales as $locale) {
                if ($user->canTranslate($locale)) {
                    $recipients[$user->getId()->toRfc4122()] = $user;
                    break;
                }
            }
        }

        return array_values($recipients);
    }
}
