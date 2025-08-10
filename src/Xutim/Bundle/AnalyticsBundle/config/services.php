<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

// use Xutim\SecurityBundle\Service\UserStorage;

return static function (ContainerConfigurator $container): void {
    $services = $container->services();

    // $services->set(UserStorage::class)
    //     ->arg('$tokenStorage', service(TokenStorageInterface::class))
    // ;
};
