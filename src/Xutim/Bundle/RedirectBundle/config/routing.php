<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Psr\Log\LoggerInterface;
use Xutim\RedirectBundle\Domain\Repository\RedirectRepositoryInterface;
use Xutim\RedirectBundle\Infra\Routing\RedirectRouteResolver;

return static function (ContainerConfigurator $container): void {
    $services = $container->services();

    $services
        ->set(RedirectRouteResolver::class)
        ->arg('$redirectRepo', service(RedirectRepositoryInterface::class))
        ->arg('$logger', service(LoggerInterface::class))
        ->tag('xutim.dynamic_route_resolver', ['priority' => 200])
    ;
};
