<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Action\Admin;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Xutim\CoreBundle\Routing\AdminUrlGenerator;
use Xutim\SecurityBundle\Service\UserStorage;

class AdminHomepageRedirectAction extends AbstractController
{
    public function __construct(
        private readonly AdminUrlGenerator $router,
        private readonly UserStorage $userStorage,
        private readonly string $defaultLocale,
    ) {
    }

    public function __invoke(): Response
    {
        $user = $this->userStorage->getUserWithException();
        if ($user->canTranslate($this->defaultLocale)) {
            $locale = $this->defaultLocale;
        } else {
            $locales = $user->getTranslationLocales();
            $locale = $locales[array_key_first($user->getTranslationLocales())];
        }

        return new RedirectResponse($this->router->generate('admin_homepage', ['_content_locale' => $locale]));
    }
}
