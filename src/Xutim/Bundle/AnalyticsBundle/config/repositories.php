<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

// use Doctrine\Persistence\ManagerRegistry;
// use Xutim\SecurityBundle\Repository\UserRepository;
// use Xutim\SecurityBundle\Repository\UserRepositoryInterface;

return static function (ContainerConfigurator $container): void {
    $services = $container->services();

    // $services->set(UserRepository::class)
    //     ->arg('$registry', service(ManagerRegistry::class))
    //     ->arg('$entityClass', '%xutim_security.model.user.class%')
    //     ->tag('doctrine.repository_service');
    //
    // $services->alias(UserRepositoryInterface::class, UserRepository::class);
};
