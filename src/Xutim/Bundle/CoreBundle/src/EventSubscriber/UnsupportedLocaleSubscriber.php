<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Webmozart\Assert\Assert;
use Xutim\CoreBundle\Context\SiteContext;

class UnsupportedLocaleSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private readonly SiteContext $siteContext,
        private readonly string $defaultLocale
    ) {
    }

    public function onKernelRequest(RequestEvent $event): void
    {
        $request = $event->getRequest();
        $path = $request->getPathInfo();

        $supportedLocales = $this->siteContext->getMainLocales();

        if (preg_match('#^/([a-z]{2})(/|$)#', $path, $matches) === 1) {
            $locale = $matches[1];
            if (!in_array($locale, $supportedLocales, true)) {
                $newPath = preg_replace("#^/{$locale}#", '/' . $this->defaultLocale, $path);
                Assert::string($newPath);
                $event->setResponse(new RedirectResponse($newPath, 302));
            }
        }
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST => ['onKernelRequest', 20],
        ];
    }
}
