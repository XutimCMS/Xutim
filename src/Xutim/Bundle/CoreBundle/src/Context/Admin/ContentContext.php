<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Context\Admin;

use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Contracts\Cache\CacheInterface;
use Xutim\CoreBundle\Exception\LogicException;
use Xutim\SecurityBundle\Service\UserStorage;

readonly class ContentContext
{
    public function __construct(
        private CacheInterface $adminContentContextCache,
        private UserStorage $userStorage,
        private RequestStack $requestStack,
        private string $defaultLocale
    ) {
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
        return $this->getLocale();
    }

    public function getLocale(): string
    {
        $request = $this->requestStack->getCurrentRequest();
        if ($request === null) {
            throw new LogicException('There is no request present.');
        }

        return $request->attributes->getString('_content_locale', $this->defaultLocale);
    }
}
