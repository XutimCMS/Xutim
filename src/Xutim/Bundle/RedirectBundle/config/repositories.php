<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Doctrine\Persistence\ManagerRegistry;
use Xutim\RedirectBundle\Domain\Repository\RedirectRepositoryInterface;
use Xutim\RedirectBundle\Infra\Doctrine\ORM\RedirectRepository;

return static function (ContainerConfigurator $container): void {
    $services = $container->services();
    $services->set(RedirectRepository::class)
        ->arg('$registry', service(ManagerRegistry::class))
        ->arg('$entityClass', '%xutim_redirect.model.redirect.class%')
        ->tag('doctrine.repository_service');

    $services->alias(RedirectRepositoryInterface::class, RedirectRepository::class);
};
