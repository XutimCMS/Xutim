<?php

declare(strict_types=1);

use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;
use Xutim\CoreBundle\Action\Admin\XutimSection\EditXutimSectionFormAction;
use Xutim\CoreBundle\Action\Admin\XutimSection\PreviewXutimSectionAction;
use Xutim\CoreBundle\Action\Admin\XutimSection\RefreshXutimSectionFormAction;
use Xutim\CoreBundle\Action\Admin\XutimSection\SaveXutimSectionAction;

return function (RoutingConfigurator $routes) {
    $routes
        ->add('admin_xutim_section_form', '/admin/{_content_locale}/xutim-section/{code}/form')
        ->methods(['get'])
        ->controller(EditXutimSectionFormAction::class)
    ;

    $routes
        ->add('admin_xutim_section_refresh', '/admin/{_content_locale}/xutim-section/{code}/refresh')
        ->methods(['post'])
        ->controller(RefreshXutimSectionFormAction::class)
    ;

    $routes
        ->add('admin_xutim_section_save', '/admin/{_content_locale}/xutim-section/{code}/save')
        ->methods(['post'])
        ->controller(SaveXutimSectionAction::class)
    ;

    $routes
        ->add('admin_xutim_section_preview', '/admin/{_content_locale}/xutim-section/{code}/preview')
        ->methods(['post'])
        ->controller(PreviewXutimSectionAction::class)
    ;
};
