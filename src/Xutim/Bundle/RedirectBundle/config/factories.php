<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Xutim\RedirectBundle\Domain\Factory\RedirectFactory;
use Xutim\RedirectBundle\Domain\Factory\RedirectFactoryInterface;

return static function (ContainerConfigurator $container): void {
    $services = $container->services();

    $services->set(RedirectFactory::class)
        ->arg('$redirectClass', '%xutim_redirect.model.redirect.class%');

    $services->alias(RedirectFactoryInterface::class, RedirectFactory::class);
};
