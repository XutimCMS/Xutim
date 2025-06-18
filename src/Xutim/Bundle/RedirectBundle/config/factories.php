<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Xutim\RedirectBundle\Domain\Factory\CmsRedirectFactory;
use Xutim\RedirectBundle\Domain\Factory\CmsRedirectFactoryInterface;

return static function (ContainerConfigurator $container): void {
    $services = $container->services();

    $services->set(CmsRedirectFactory::class)
        ->arg('$redirectClass', '%xutim_redirect.model.redirect.class%');

    $services->alias(CmsRedirectFactoryInterface::class, CmsRedirectFactory::class);
};
