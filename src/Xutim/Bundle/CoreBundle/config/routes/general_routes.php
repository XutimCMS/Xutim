<?php

declare(strict_types=1);

use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;
use Xutim\CoreBundle\Action\Admin\AdminHomepageAction;
use Xutim\CoreBundle\Action\Admin\AdminHomepageRedirectAction;
use Xutim\CoreBundle\Action\Admin\DesignTestAction;

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

    $routes
        ->add('admin_design_test', '/admin/{_content_locale}/design-test')
        ->methods(['get'])
        ->controller(DesignTestAction::class)
    ;
};
