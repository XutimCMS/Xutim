<?php

declare(strict_types=1);

use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;
use Xutim\CoreBundle\Action\Admin\Menu\CreateMenuItemAction;
use Xutim\CoreBundle\Action\Admin\Menu\DeleteMenuItemAction;
use Xutim\CoreBundle\Action\Admin\Menu\EditMenuItemAction;
use Xutim\CoreBundle\Action\Admin\Menu\MoveMenuItemAction;
use Xutim\CoreBundle\Action\Admin\Menu\ShowMenuAction;

return function (RoutingConfigurator $routes) {
    $routes
        ->add('admin_menu_item_new', '/admin/{_content_locale}/menu/new-item/{id?}')
        ->methods(['get', 'post'])
        ->controller(CreateMenuItemAction::class)
    ;

    $routes
        ->add('admin_menu_item_delete', '/admin/{_content_locale}/menu/delete-item/{id}')
        ->methods(['get', 'post'])
        ->controller(DeleteMenuItemAction::class)
    ;

    $routes
        ->add('admin_menu_item_edit', '/admin/{_content_locale}/menu/edit/{id}')
        ->methods(['get', 'post'])
        ->controller(EditMenuItemAction::class)
    ;

    $routes
        ->add('admin_menu_item_move', '/admin/{_content_locale}/menu/{id}/move/{dir}')
        ->methods(['get', 'post'])
        ->controller(MoveMenuItemAction::class)
    ;

    $routes
        ->add('admin_menu_list', '/admin/{_content_locale}/menu/{id?}')
        ->methods(['get'])
        ->controller(ShowMenuAction::class)
    ;
};
