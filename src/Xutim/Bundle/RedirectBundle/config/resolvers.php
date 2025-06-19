<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Symfony\Component\Routing\RouterInterface;
use Xutim\RedirectBundle\Infra\Resolver\RedirectTargetResolver;
use Xutim\RedirectComponent\Domain\RedirectTargetResolverInterface;
use Xutim\RedirectComponent\Infra\RedirectResolver;

return static function (ContainerConfigurator $container): void {
    $services = $container->services();

    $services->set(RedirectTargetResolver::class)
        ->arg('$router', service(RouterInterface::class))
        ->tag('redirect.target_resolver');

    $services->instanceof(RedirectTargetResolverInterface::class)
        ->tag('redirect.target_resolver');

    $services->set(RedirectResolver::class)
        ->args([tagged_iterator('redirect.target_resolver')]);
};
