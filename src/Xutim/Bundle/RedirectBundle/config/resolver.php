<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Symfony\Component\Routing\RouterInterface;
use Xutim\RedirectBundle\Infrastructure\Resolver\CmsRedirectTargetResolver;

return static function (ContainerConfigurator $container): void {
    $services = $container->services();

    $services->set(CmsRedirectTargetResolver::class)
        ->arg('$router', RouterInterface::class)
        ->tag('redirect.target_resolver');
};
