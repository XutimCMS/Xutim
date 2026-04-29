<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\EventSubscriber;

use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Xutim\CoreBundle\Routing\Dynamic\DynamicRouteResolverInterface;

final readonly class DynamicRouteSubscriber implements EventSubscriberInterface
{
    /**
     * @param iterable<DynamicRouteResolverInterface> $resolvers
     */
    public function __construct(
        private iterable $resolvers,
        private LoggerInterface $logger
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [KernelEvents::REQUEST => ['onKernelRequest', 33]];
    }

    public function onKernelRequest(RequestEvent $event): void
    {
        $request = $event->getRequest();

        if (!$event->isMainRequest()) {
            return;
        }

        $path = $request->getPathInfo();
        foreach ($this->resolvers as $resolver) {
            try {
                $match = $resolver->resolve($path, $request);
            } catch (\Throwable $e) {
                $this->logger->error('Dynamic route resolver failed', [
                    'resolver' => $resolver::class,
                    'exception' => $e
                ]);
                continue;
            }
            if ($match === null) {
                continue;
            }

            $match->apply($event);

            return;
        }
    }
}
