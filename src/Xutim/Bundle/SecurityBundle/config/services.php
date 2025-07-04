<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Xutim\SecurityBundle\Service\TranslatorAuthChecker;
use Xutim\SecurityBundle\Service\UserStorage;
use Xutim\SecurityBundle\Service\UserStorage as ServiceUserStorage;

return static function (ContainerConfigurator $container): void {
    $services = $container->services();

    $services->set(UserStorage::class)
        ->arg('$tokenStorage', service(TokenStorageInterface::class))
    ;

    $services->set(TranslatorAuthChecker::class)
        ->arg('$userStorage', service(ServiceUserStorage::class))
        ->arg('$security', service(Security::class))
    ;
};
