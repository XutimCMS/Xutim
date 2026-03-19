<?php

declare(strict_types=1);

use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;

return function (RoutingConfigurator $routes) {
    $requirements = ['_content_locale' => '[a-z]{2}(?:_[A-Za-z]{2,8})*'];
    $defaults = ['_content_locale' => '%kernel.default_locale%'];

    $routes->import('routes/notification_routes.php')
        ->requirements($requirements)
        ->defaults($defaults)
    ;
};
