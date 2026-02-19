<?php

declare(strict_types=1);

namespace Xutim\SecurityBundle\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Routing\RouterInterface;
use Xutim\SecurityBundle\Service\TranslatorAuthChecker;
use Xutim\SecurityBundle\Service\UserStorage;

final class AdminContentLocaleRedirectSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private readonly TranslatorAuthChecker $translatorAuthChecker,
        private readonly UserStorage $userStorage,
        private readonly RouterInterface $router,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST => ['onKernelRequest', 7],
        ];
    }

    public function onKernelRequest(RequestEvent $event): void
    {
        if (!$event->isMainRequest()) {
            return;
        }

        $request = $event->getRequest();
        $contentLocale = $request->attributes->getString('_content_locale');

        if ($contentLocale === '') {
            return;
        }

        $route = $request->attributes->getString('_route');
        $routeDefinition = $this->router->getRouteCollection()->get($route);

        if ($routeDefinition === null) {
            return;
        }

        if (!in_array('_content_locale', $routeDefinition->compile()->getVariables(), true)) {
            return;
        }

        $user = $this->userStorage->getUser();

        if ($user === null) {
            return;
        }

        if (!$user->isTranslator()) {
            return;
        }

        if ($this->translatorAuthChecker->canTranslate($contentLocale)) {
            return;
        }

        $allowedLocales = $user->getTranslationLocales();

        if ($allowedLocales === []) {
            return;
        }

        $routeParams = $request->attributes->all('_route_params');
        $routeParams['_content_locale'] = $allowedLocales[0];

        $query = $request->query->all();
        unset($query['_content_locale']);

        $url = $this->router->generate($route, array_merge($query, $routeParams));

        $event->setResponse(new RedirectResponse($url));
    }
}
