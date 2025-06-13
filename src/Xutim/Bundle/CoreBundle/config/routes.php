<?php

declare(strict_types=1);

use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;

return function (RoutingConfigurator $routes) {
    $routes->import('.', 'snippet_routes');
    $routes->import('@XutimCoreBundle/src/Action/Admin', 'attribute')
        ->prefix('/admin')
    ;
    $routes->import('@XutimCoreBundle/src/Action/Public', 'attribute');
    $routes->import('.', 'content_translation_fallback');
};
