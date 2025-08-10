<?php

declare(strict_types=1);

use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;
use Xutim\AnalyticsBundle\Action\Public\CollectAnalyticsAction;

return function (RoutingConfigurator $routes) {
    $routes->add('xutim_analytics_collect', '/_analytics/collect')
        ->methods(['post'])
        ->controller(CollectAnalyticsAction::class);
};
