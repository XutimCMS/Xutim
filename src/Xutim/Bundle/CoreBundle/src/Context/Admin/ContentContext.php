<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Context\Admin;

use Symfony\Contracts\Cache\CacheInterface;
use Xutim\CoreBundle\Security\UserStorage;

readonly class ContentContext
{
    public function __construct(private CacheInterface $adminContentContextCache, private UserStorage $userStorage)
    {
    }

    public function changeLanguage(string $locale): void
    {
        $user = $this->userStorage->getUserWithException();
        $ref = 'language-' . $user->getId()->toRfc4122();

        $this->adminContentContextCache->delete($ref);
        $this->adminContentContextCache->get($ref, fn () => $locale);
    }

    public function getLanguage(): string
    {
        $user = $this->userStorage->getUserWithException();
        $ref = 'language-' . $user->getId()->toRfc4122();

        return $this->adminContentContextCache->get($ref, fn () => 'en');
    }
}
