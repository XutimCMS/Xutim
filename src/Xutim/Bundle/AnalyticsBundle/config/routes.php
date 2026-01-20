<?php

declare(strict_types=1);

use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;
use Xutim\AnalyticsBundle\Action\Admin\DashboardAction;
use Xutim\AnalyticsBundle\Action\Admin\DevicesAction;
use Xutim\AnalyticsBundle\Action\Admin\GeographyAction;
use Xutim\AnalyticsBundle\Action\Admin\PagesListAction;
use Xutim\AnalyticsBundle\Action\Admin\SinglePageAction;
use Xutim\AnalyticsBundle\Action\Admin\TrafficSourcesAction;
use Xutim\AnalyticsBundle\Action\Public\CollectAnalyticsAction;

return function (RoutingConfigurator $routes) {
    $routes->add('xutim_analytics_collect', '/_analytics/collect')
        ->methods(['post'])
        ->controller(CollectAnalyticsAction::class);

    $routes->add('admin_analytics_dashboard', '/admin/analytics')
        ->methods(['get'])
        ->controller(DashboardAction::class);

    $routes->add('admin_analytics_pages', '/admin/analytics/pages')
        ->methods(['get'])
        ->controller(PagesListAction::class);

    $routes->add('admin_analytics_sources', '/admin/analytics/sources')
        ->methods(['get'])
        ->controller(TrafficSourcesAction::class);

    $routes->add('admin_analytics_geography', '/admin/analytics/geography')
        ->methods(['get'])
        ->controller(GeographyAction::class);

    $routes->add('admin_analytics_devices', '/admin/analytics/devices')
        ->methods(['get'])
        ->controller(DevicesAction::class);

    $routes->add('admin_analytics_page', '/admin/analytics/page')
        ->methods(['get'])
        ->controller(SinglePageAction::class);
};
