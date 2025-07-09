<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\Routing\RouterInterface;
use Xutim\CoreBundle\Context\SiteContext;

class LocaleRedirectSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private readonly RouterInterface $router,
        private readonly SiteContext $siteContext,
        private readonly string $defaultLocale = 'en',
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            'kernel.request' => [['onKernelRequest', 20]],
        ];
    }

    public function onKernelRequest(RequestEvent $event): void
    {
        $request = $event->getRequest();

        // Only run on master requests
        if (!$event->isMainRequest()) {
            return;
        }

        // Redirect only if we are at root path
        if ('/' !== $request->getPathInfo()) {
            return;
        }

        $preferredLocale = $this->getPreferredLocale($request->getLanguages());

        $url = $this->router->generate('homepage', ['_locale' => $preferredLocale]);
        $event->setResponse(new RedirectResponse($url));
    }

    /**
     * @param array<string> $browserLanguages
     */
    private function getPreferredLocale(array $browserLanguages): string
    {
        $supportedLocales = $this->siteContext->getMainLocales();

        foreach ($browserLanguages as $lang) {
            $base = substr($lang, 0, 2);
            if (in_array($base, $supportedLocales, true)) {
                return $base;
            }
        }

        return $this->defaultLocale;
    }
}
