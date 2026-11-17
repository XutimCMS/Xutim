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
    ) {
    }

    public function onKernelRequest(RequestEvent $event): void
    {
        $request = $event->getRequest();
        $path = $request->getPathInfo();

        if (preg_match('#^/([a-z]{2})(/|$)#', $path, $matches) !== 1) {
            return;
        }

        $supportedLocales = $this->siteContext->getMainLocales();
        if ($supportedLocales === [] || in_array($matches[1], $supportedLocales, true)) {
            return;
        }

        $newPath = preg_replace('#^/[a-z]{2}#', '/' . $this->redirectLocale($supportedLocales), $path);
        Assert::string($newPath);
        $event->setResponse(new RedirectResponse($newPath, 302));
    }

    /**
     * @param non-empty-array<string> $supportedLocales
     */
    private function redirectLocale(array $supportedLocales): string
    {
        $referenceLocale = $this->siteContext->getReferenceLocale();

        return in_array($referenceLocale, $supportedLocales, true) ? $referenceLocale : reset($supportedLocales);
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST => ['onKernelRequest', 20],
        ];
    }
}
