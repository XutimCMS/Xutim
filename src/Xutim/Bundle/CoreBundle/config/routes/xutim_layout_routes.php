<?php

declare(strict_types=1);

use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;
use Xutim\CoreBundle\Action\Admin\XutimLayout\EditXutimLayoutFormAction;
use Xutim\CoreBundle\Action\Admin\XutimLayout\PreviewXutimLayoutAction;
use Xutim\CoreBundle\Action\Admin\XutimLayout\RefreshXutimLayoutFormAction;
use Xutim\CoreBundle\Action\Admin\XutimLayout\SaveXutimLayoutAction;

return function (RoutingConfigurator $routes) {
    $routes
        ->add('admin_xutim_layout_form', '/admin/{_content_locale}/xutim-layout/{code}/form')
        ->methods(['get'])
        ->controller(EditXutimLayoutFormAction::class)
    ;

    $routes
        ->add('admin_xutim_layout_refresh', '/admin/{_content_locale}/xutim-layout/{code}/refresh')
        ->methods(['post'])
        ->controller(RefreshXutimLayoutFormAction::class)
    ;

    $routes
        ->add('admin_xutim_layout_save', '/admin/{_content_locale}/xutim-layout/{code}/save')
        ->methods(['post'])
        ->controller(SaveXutimLayoutAction::class)
    ;

    $routes
        ->add('admin_xutim_layout_preview', '/admin/{_content_locale}/xutim-layout/{code}/preview')
        ->methods(['post'])
        ->controller(PreviewXutimLayoutAction::class)
    ;
};
