<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Doctrine\Persistence\ManagerRegistry;
use Xutim\AnalyticsBundle\Domain\Repository\AnalyticsEventRepositoryInterface;
use Xutim\AnalyticsBundle\Repository\AnalyticsEventRepository;

return static function (ContainerConfigurator $container): void {
    $services = $container->services();

    $services->set(AnalyticsEventRepository::class)
        ->arg('$registry', service(ManagerRegistry::class))
        ->arg('$entityClass', '%xutim_analytics.model.analytics_event.class%')
        ->tag('doctrine.repository_service');

    $services->alias(AnalyticsEventRepositoryInterface::class, AnalyticsEventRepository::class);
};
