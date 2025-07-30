<?php

declare(strict_types=1);

use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;
use Xutim\CoreBundle\Action\Admin\Block\CreateBlockAction;
use Xutim\CoreBundle\Action\Admin\Block\DeleteBlockAction;
use Xutim\CoreBundle\Action\Admin\Block\EditBlockAction;
use Xutim\CoreBundle\Action\Admin\Block\ListBlocksAction;
use Xutim\CoreBundle\Action\Admin\Block\ShowBlockAction;
use Xutim\CoreBundle\Action\Admin\BlockItem\AddBlockItemAction;
use Xutim\CoreBundle\Action\Admin\BlockItem\EditBlockItemAction;
use Xutim\CoreBundle\Action\Admin\BlockItem\RemoveBlockItemAction;
use Xutim\CoreBundle\Action\Admin\BlockItem\ReorderBlockItemsAction;

return function (RoutingConfigurator $routes) {
    $routes
        ->add('admin_block_new', '/admin/{_content_locale}/block/new')
        ->methods(['get', 'post'])
        ->controller(CreateBlockAction::class)
    ;

    $routes
        ->add('admin_block_delete', '/admin/{_content_locale}/block/delete/{id}')
        ->methods(['get', 'post'])
        ->controller(DeleteBlockAction::class)
    ;

    $routes
        ->add('admin_block_edit', '/admin/{_content_locale}/block/edit/{id}')
        ->methods(['get', 'post'])
        ->controller(EditBlockAction::class)
    ;

    $routes
        ->add('admin_block_list', '/admin/{_content_locale}/block')
        ->methods(['get'])
        ->controller(ListBlocksAction::class)
    ;

    $routes
        ->add('admin_block_show', '/admin/{_content_locale}/block/{id<[^/]+>}')
        ->methods(['get'])
        ->controller(ShowBlockAction::class)
    ;

    $routes
        ->add('admin_block_add_item', '/admin/{_content_locale}/block/add-item/{id}')
        ->methods(['get', 'post'])
        ->controller(AddBlockItemAction::class)
    ;

    $routes
        ->add('admin_block_edit_item', '/admin/{_content_locale}/block/edit-item/{id}')
        ->methods(['get', 'post'])
        ->controller(EditBlockItemAction::class)
    ;

    $routes
        ->add('admin_block_remove_item', '/admin/{_content_locale}/block/remove-item/{id}')
        ->methods(['post'])
        ->controller(RemoveBlockItemAction::class)
    ;

    $routes
        ->add('admin_block_reorder_item', '/admin/{_content_locale}/block/reorder/{id}')
        ->methods(['post'])
        ->controller(ReorderBlockItemsAction::class)
    ;
};
