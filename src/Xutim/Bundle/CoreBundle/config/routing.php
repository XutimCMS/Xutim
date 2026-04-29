<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Psr\Log\LoggerInterface;
use Xutim\CoreBundle\EventSubscriber\DynamicRouteSubscriber;
use Xutim\CoreBundle\Routing\Dynamic\DynamicRouteResolverInterface;

return static function (ContainerConfigurator $container): void {
    $services = $container->services();

    $services->instanceof(DynamicRouteResolverInterface::class)
        ->tag('xutim.dynamic_route_resolver');

    $services->set(DynamicRouteSubscriber::class)
        ->arg('$logger', service(LoggerInterface::class))
        ->arg('$resolvers', tagged_iterator('xutim.dynamic_route_resolver'))
        ->tag('kernel.event_subscriber');
};
