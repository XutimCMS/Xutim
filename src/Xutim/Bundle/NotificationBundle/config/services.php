<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Xutim\CoreBundle\MessageHandler\CommandHandlerInterface;

return static function (ContainerConfigurator $container): void {
    $services = $container->services();

    $services->instanceof(CommandHandlerInterface::class)
        ->tag('messenger.message_handler', ['bus' => 'command.bus']);

    $services
        ->defaults()
        ->autowire()
        ->autoconfigure()
    ;

    $services->load('Xutim\\NotificationBundle\\', '../src/')
        ->exclude('../src/{DependencyInjection,Entity}');

    $services->set(\Xutim\NotificationBundle\Service\AdminAlertService::class)
        ->autowire()
        ->public();
};
