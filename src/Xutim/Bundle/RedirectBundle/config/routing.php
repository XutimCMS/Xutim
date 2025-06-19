<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Xutim\RedirectBundle\Domain\Repository\RedirectRepositoryInterface;
use Xutim\RedirectBundle\Infra\Routing\RedirectRouteLoader;
use Xutim\RedirectBundle\Infra\Routing\RedirectRouteService;

return static function (ContainerConfigurator $container): void {
    $services = $container->services();

    $services
        ->set(RedirectRouteLoader::class)
        ->arg('$repo', service(RedirectRepositoryInterface::class))
        ->arg('$router', service(UrlGeneratorInterface::class))
        ->arg('$redirectVersionPath', '%redirect_routes_version_file%')
        ->arg('$env', '%kernel.environment%')
        ->tag('routing.loader', ['priority' => 1000])
    ;

    $services
        ->set(RedirectRouteService::class)
        ->arg('$redirectVersionPath', '%redirect_routes_version_file%')
    ;
};
