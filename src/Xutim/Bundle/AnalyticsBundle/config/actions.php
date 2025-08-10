<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Symfony\Component\Messenger\MessageBusInterface;
use Xutim\AnalyticsBundle\Action\Public\CollectAnalyticsAction;

return static function (ContainerConfigurator $container): void {
    $services = $container->services();

    $services->set(CollectAnalyticsAction::class)
        ->arg('$bus', service(MessageBusInterface::class))
        ->tag('controller.service_arguments')
    ;
};
