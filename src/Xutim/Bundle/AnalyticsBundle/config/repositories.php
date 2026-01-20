<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Doctrine\Persistence\ManagerRegistry;
use Xutim\AnalyticsBundle\Domain\Repository\AnalyticsDailyCountryRepositoryInterface;
use Xutim\AnalyticsBundle\Domain\Repository\AnalyticsDailyDeviceRepositoryInterface;
use Xutim\AnalyticsBundle\Domain\Repository\AnalyticsDailyPageReferrerRepositoryInterface;
use Xutim\AnalyticsBundle\Domain\Repository\AnalyticsDailySessionRepositoryInterface;
use Xutim\AnalyticsBundle\Domain\Repository\AnalyticsDailySummaryRepositoryInterface;
use Xutim\AnalyticsBundle\Domain\Repository\AnalyticsDailyTrafficSourceRepositoryInterface;
use Xutim\AnalyticsBundle\Domain\Repository\AnalyticsDailyUtmRepositoryInterface;
use Xutim\AnalyticsBundle\Domain\Repository\AnalyticsEventArchiveRepositoryInterface;
use Xutim\AnalyticsBundle\Domain\Repository\AnalyticsEventRepositoryInterface;
use Xutim\AnalyticsBundle\Repository\AnalyticsDailyCountryRepository;
use Xutim\AnalyticsBundle\Repository\AnalyticsDailyDeviceRepository;
use Xutim\AnalyticsBundle\Repository\AnalyticsDailyPageReferrerRepository;
use Xutim\AnalyticsBundle\Repository\AnalyticsDailySessionRepository;
use Xutim\AnalyticsBundle\Repository\AnalyticsDailySummaryRepository;
use Xutim\AnalyticsBundle\Repository\AnalyticsDailyTrafficSourceRepository;
use Xutim\AnalyticsBundle\Repository\AnalyticsDailyUtmRepository;
use Xutim\AnalyticsBundle\Repository\AnalyticsEventArchiveRepository;
use Xutim\AnalyticsBundle\Repository\AnalyticsEventRepository;

return static function (ContainerConfigurator $container): void {
    $services = $container->services();

    $services->set(AnalyticsEventRepository::class)
        ->arg('$registry', service(ManagerRegistry::class))
        ->arg('$entityClass', '%xutim_analytics.model.analytics_event.class%')
        ->tag('doctrine.repository_service');

    $services->alias(AnalyticsEventRepositoryInterface::class, AnalyticsEventRepository::class);

    $services->set(AnalyticsEventArchiveRepository::class)
        ->arg('$registry', service(ManagerRegistry::class))
        ->arg('$entityClass', '%xutim_analytics.model.analytics_event_archive.class%')
        ->tag('doctrine.repository_service');

    $services->alias(AnalyticsEventArchiveRepositoryInterface::class, AnalyticsEventArchiveRepository::class);

    $services->set(AnalyticsDailySummaryRepository::class)
        ->arg('$registry', service(ManagerRegistry::class))
        ->arg('$entityClass', '%xutim_analytics.model.analytics_daily_summary.class%')
        ->tag('doctrine.repository_service');

    $services->alias(AnalyticsDailySummaryRepositoryInterface::class, AnalyticsDailySummaryRepository::class);

    $services->set(AnalyticsDailyPageReferrerRepository::class)
        ->arg('$registry', service(ManagerRegistry::class))
        ->arg('$entityClass', '%xutim_analytics.model.analytics_daily_page_referrer.class%')
        ->tag('doctrine.repository_service');

    $services->alias(AnalyticsDailyPageReferrerRepositoryInterface::class, AnalyticsDailyPageReferrerRepository::class);

    $services->set(AnalyticsDailyTrafficSourceRepository::class)
        ->arg('$registry', service(ManagerRegistry::class))
        ->arg('$entityClass', '%xutim_analytics.model.analytics_daily_traffic_source.class%')
        ->tag('doctrine.repository_service');

    $services->alias(AnalyticsDailyTrafficSourceRepositoryInterface::class, AnalyticsDailyTrafficSourceRepository::class);

    $services->set(AnalyticsDailyCountryRepository::class)
        ->arg('$registry', service(ManagerRegistry::class))
        ->arg('$entityClass', '%xutim_analytics.model.analytics_daily_country.class%')
        ->tag('doctrine.repository_service');

    $services->alias(AnalyticsDailyCountryRepositoryInterface::class, AnalyticsDailyCountryRepository::class);

    $services->set(AnalyticsDailyDeviceRepository::class)
        ->arg('$registry', service(ManagerRegistry::class))
        ->arg('$entityClass', '%xutim_analytics.model.analytics_daily_device.class%')
        ->tag('doctrine.repository_service');

    $services->alias(AnalyticsDailyDeviceRepositoryInterface::class, AnalyticsDailyDeviceRepository::class);

    $services->set(AnalyticsDailyUtmRepository::class)
        ->arg('$registry', service(ManagerRegistry::class))
        ->arg('$entityClass', '%xutim_analytics.model.analytics_daily_utm.class%')
        ->tag('doctrine.repository_service');

    $services->alias(AnalyticsDailyUtmRepositoryInterface::class, AnalyticsDailyUtmRepository::class);

    $services->set(AnalyticsDailySessionRepository::class)
        ->arg('$registry', service(ManagerRegistry::class))
        ->arg('$entityClass', '%xutim_analytics.model.analytics_daily_session.class%')
        ->tag('doctrine.repository_service');

    $services->alias(AnalyticsDailySessionRepositoryInterface::class, AnalyticsDailySessionRepository::class);
};
