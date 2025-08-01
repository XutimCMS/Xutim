<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Symfony\Contracts\Cache\CacheInterface;
use Xutim\CoreBundle\Scheduled\XutimSchedulerProvider;

return static function (ContainerConfigurator $container): void {
    $services = $container->services();

    $services
        ->set(XutimSchedulerProvider::class)
        ->arg('$cache', service(CacheInterface::class))
        ->tag('scheduler.schedule_provider', ['name' => 'xutim_core'])
    ;
};
