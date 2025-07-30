<?php

declare(strict_types=1);

use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;
use Xutim\CoreBundle\Action\Admin\AdminHomepageAction;
use Xutim\CoreBundle\Action\Admin\AdminHomepageRedirectAction;

return function (RoutingConfigurator $routes) {
    $routes
        ->add('admin_homepage', '/admin/{_content_locale}/')
        ->methods(['get'])
        ->controller(AdminHomepageAction::class)
    ;

    $routes
        ->add('admin_homepage_redirect', '/admin/')
        ->methods(['get'])
        ->controller(AdminHomepageRedirectAction::class)
    ;
};
