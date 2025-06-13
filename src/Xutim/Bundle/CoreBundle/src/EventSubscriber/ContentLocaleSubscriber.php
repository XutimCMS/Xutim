<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class ContentLocaleSubscriber implements EventSubscriberInterface
{
    public function onKernelRequest(RequestEvent $event): void
    {
        $req = $event->getRequest();
        $req->attributes->set('contentLocale', $req->attributes->get('_content_locale') ?? $req->getLocale());
    }

    public static function getSubscribedEvents(): array
    {
        return [KernelEvents::REQUEST => ['onKernelRequest', 15]];
    }
}
