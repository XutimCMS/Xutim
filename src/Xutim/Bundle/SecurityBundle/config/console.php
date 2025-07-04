<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Symfony\Component\Messenger\MessageBusInterface;
use Xutim\SecurityBundle\Console\CreateUserCliCommand;
use Xutim\SecurityBundle\Repository\UserRepositoryInterface;

return static function (ContainerConfigurator $container): void {
    $services = $container->services();

    $services->set(CreateUserCliCommand::class)
        ->arg('$commandBus', service(MessageBusInterface::class))
        ->arg('$userRepository', service(UserRepositoryInterface::class))
        ->tag('console.command')
    ;
};
