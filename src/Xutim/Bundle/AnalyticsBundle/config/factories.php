<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Xutim\AnalyticsBundle\Domain\Factory\AnalyticsEventFactoryInterface;
use Xutim\AnalyticsBundle\Factory\AnalyticsEventFactory;

return function (ContainerConfigurator $configurator) {
    $services = $configurator->services();

    $services->set(AnalyticsEventFactory::class)
        ->arg('$entityClass', '%xutim_analytics.model.analytics_event.class%');

    $services->alias(AnalyticsEventFactoryInterface::class, AnalyticsEventFactory::class);
};
