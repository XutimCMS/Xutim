<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\UX\Chartjs\Builder\ChartBuilderInterface;
use Twig\Environment;
use Xutim\AnalyticsBundle\Action\Admin\DashboardAction;
use Xutim\AnalyticsBundle\Action\Admin\DevicesAction;
use Xutim\AnalyticsBundle\Action\Admin\GeographyAction;
use Xutim\AnalyticsBundle\Action\Admin\PagesListAction;
use Xutim\AnalyticsBundle\Action\Admin\SidebarWidgetAction;
use Xutim\AnalyticsBundle\Action\Admin\SinglePageAction;
use Xutim\AnalyticsBundle\Action\Admin\TrafficSourcesAction;
use Xutim\AnalyticsBundle\Action\Public\CollectAnalyticsAction;
use Xutim\AnalyticsBundle\Domain\Repository\AnalyticsDailyCountryRepositoryInterface;
use Xutim\AnalyticsBundle\Domain\Repository\AnalyticsDailyDeviceRepositoryInterface;
use Xutim\AnalyticsBundle\Domain\Repository\AnalyticsDailyPageReferrerRepositoryInterface;
use Xutim\AnalyticsBundle\Domain\Repository\AnalyticsDailySessionRepositoryInterface;
use Xutim\AnalyticsBundle\Domain\Repository\AnalyticsDailySummaryRepositoryInterface;
use Xutim\AnalyticsBundle\Domain\Repository\AnalyticsDailyTrafficSourceRepositoryInterface;
use Xutim\AnalyticsBundle\Service\DateRangeResolver;

return static function (ContainerConfigurator $container): void {
    $services = $container->services();

    $services->set(CollectAnalyticsAction::class)
        ->arg('$bus', service(MessageBusInterface::class))
        ->tag('controller.service_arguments')
    ;

    $services->set(DashboardAction::class)
        ->arg('$summaryRepo', service(AnalyticsDailySummaryRepositoryInterface::class))
        ->arg('$trafficSourceRepo', service(AnalyticsDailyTrafficSourceRepositoryInterface::class))
        ->arg('$countryRepo', service(AnalyticsDailyCountryRepositoryInterface::class))
        ->arg('$deviceRepo', service(AnalyticsDailyDeviceRepositoryInterface::class))
        ->arg('$sessionRepo', service(AnalyticsDailySessionRepositoryInterface::class))
        ->arg('$dateRangeResolver', service(DateRangeResolver::class))
        ->arg('$twig', service(Environment::class))
        ->arg('$chartBuilder', service(ChartBuilderInterface::class)->ignoreOnInvalid())
        ->tag('controller.service_arguments')
    ;

    $services->set(PagesListAction::class)
        ->arg('$summaryRepo', service(AnalyticsDailySummaryRepositoryInterface::class))
        ->arg('$dateRangeResolver', service(DateRangeResolver::class))
        ->arg('$twig', service(Environment::class))
        ->tag('controller.service_arguments')
    ;

    $services->set(TrafficSourcesAction::class)
        ->arg('$trafficSourceRepo', service(AnalyticsDailyTrafficSourceRepositoryInterface::class))
        ->arg('$pageReferrerRepo', service(AnalyticsDailyPageReferrerRepositoryInterface::class))
        ->arg('$dateRangeResolver', service(DateRangeResolver::class))
        ->arg('$twig', service(Environment::class))
        ->tag('controller.service_arguments')
    ;

    $services->set(GeographyAction::class)
        ->arg('$countryRepo', service(AnalyticsDailyCountryRepositoryInterface::class))
        ->arg('$dateRangeResolver', service(DateRangeResolver::class))
        ->arg('$twig', service(Environment::class))
        ->tag('controller.service_arguments')
    ;

    $services->set(DevicesAction::class)
        ->arg('$deviceRepo', service(AnalyticsDailyDeviceRepositoryInterface::class))
        ->arg('$dateRangeResolver', service(DateRangeResolver::class))
        ->arg('$twig', service(Environment::class))
        ->tag('controller.service_arguments')
    ;

    $services->set(SinglePageAction::class)
        ->arg('$summaryRepo', service(AnalyticsDailySummaryRepositoryInterface::class))
        ->arg('$pageReferrerRepo', service(AnalyticsDailyPageReferrerRepositoryInterface::class))
        ->arg('$sessionRepo', service(AnalyticsDailySessionRepositoryInterface::class))
        ->arg('$dateRangeResolver', service(DateRangeResolver::class))
        ->arg('$twig', service(Environment::class))
        ->arg('$chartBuilder', service(ChartBuilderInterface::class)->ignoreOnInvalid())
        ->tag('controller.service_arguments')
    ;

    $services->set(SidebarWidgetAction::class)
        ->arg('$summaryRepo', service(AnalyticsDailySummaryRepositoryInterface::class))
        ->arg('$sessionRepo', service(AnalyticsDailySessionRepositoryInterface::class))
        ->arg('$twig', service(Environment::class))
        ->tag('controller.service_arguments')
    ;
};
