<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Doctrine\Persistence\ManagerRegistry;
use Xutim\NotificationBundle\Repository\NotificationRepository;

return static function (ContainerConfigurator $container): void {
    $services = $container->services();

    $services->set(NotificationRepository::class)
        ->arg('$registry', service(ManagerRegistry::class))
        ->arg('$entityClass', '%xutim_notification.model.notification.class%')
        ->tag('doctrine.repository_service');
};
