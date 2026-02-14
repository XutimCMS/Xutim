<?php

declare(strict_types=1);

use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;
use Xutim\CoreBundle\Action\Admin\Media\JsonListFilesAction;
use Xutim\CoreBundle\Action\Admin\Media\JsonListImagesAction;
use Xutim\CoreBundle\Action\Admin\Media\JsonShowFileAction;

return function (RoutingConfigurator $routes) {
    $routes
        ->add('admin_json_file_list', '/admin/{_content_locale}/json/file/list')
        ->methods(['get'])
        ->controller(JsonListFilesAction::class)
    ;

    $routes
        ->add('admin_json_image_list', '/admin/{_content_locale}/json/image/list')
        ->methods(['get'])
        ->controller(JsonListImagesAction::class)
    ;

    $routes
        ->add('admin_json_file_show', '/admin/{_content_locale}/json/file/show/{id}')
        ->methods(['get'])
        ->controller(JsonShowFileAction::class)
    ;
};
