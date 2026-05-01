<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Psr\Log\LoggerInterface;
use Xutim\SnippetBundle\Domain\Repository\SnippetRepositoryInterface;
use Xutim\SnippetBundle\Routing\SnippetRouteResolver;
use Xutim\SnippetBundle\Routing\SnippetUrlGenerator;

return static function (ContainerConfigurator $container): void {
    $services = $container->services();

    $services
        ->set(SnippetRouteResolver::class)
        ->arg('$snippetRepo', service(SnippetRepositoryInterface::class))
        ->arg('$logger', service(LoggerInterface::class))
        ->tag('xutim.dynamic_route_resolver', ['priority' => 100])
    ;

    $services
        ->set(SnippetUrlGenerator::class)
        ->arg('$snippetRepo', service(SnippetRepositoryInterface::class))
    ;
};
