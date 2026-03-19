<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Xutim\NotificationBundle\Domain\Factory\NotificationFactory;

return static function (ContainerConfigurator $container): void {
    $services = $container->services();

    $services->set(NotificationFactory::class)
        ->arg('$entityClass', '%xutim_notification.model.notification.class%');
};
