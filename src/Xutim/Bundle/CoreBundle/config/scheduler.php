<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Symfony\Contracts\Cache\CacheInterface;
use Xutim\CoreBundle\Scheduled\XutimSchedulerProvider;
use Xutim\CoreBundle\Scheduler\ScheduleContributorInterface;

return static function (ContainerConfigurator $container): void {
    $services = $container->services();

    $services
        ->instanceof(ScheduleContributorInterface::class)
        ->tag('xutim.schedule_contributor')
    ;

    $services
        ->set(XutimSchedulerProvider::class)
        ->arg('$cache', service(CacheInterface::class))
        ->arg('$contributors', tagged_iterator('xutim.schedule_contributor'))
        ->tag('scheduler.schedule_provider', ['name' => 'xutim_core'])
    ;
};
