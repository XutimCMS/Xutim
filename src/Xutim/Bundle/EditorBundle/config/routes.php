<?php

declare(strict_types=1);

use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;
use Xutim\EditorBundle\Action\Admin\BlockAddAction;
use Xutim\EditorBundle\Action\Admin\BlockConvertAction;
use Xutim\EditorBundle\Action\Admin\BlockCreateInlineAction;
use Xutim\EditorBundle\Action\Admin\BlockDeleteAction;
use Xutim\EditorBundle\Action\Admin\BlockEditAction;
use Xutim\EditorBundle\Action\Admin\BlockMoveAction;
use Xutim\EditorBundle\Action\Admin\BlockPatchAction;
use Xutim\EditorBundle\Action\Admin\BlockPickerAction;
use Xutim\EditorBundle\Action\Admin\BlockViewAction;

return static function (RoutingConfigurator $routes): void {
    $routes->add('admin_editor_block_view', '/admin/editor/block/{id}')
        ->methods(['GET'])
        ->controller(BlockViewAction::class);

    $routes->add('admin_editor_block_patch', '/admin/editor/block/{id}')
        ->methods(['PATCH'])
        ->controller(BlockPatchAction::class);

    $routes->add('admin_editor_block_convert', '/admin/editor/block/{id}/convert')
        ->methods(['PATCH'])
        ->controller(BlockConvertAction::class);

    $routes->add('admin_editor_block_create_inline', '/admin/editor/draft/{draftId}/block/create-inline')
        ->methods(['POST'])
        ->controller(BlockCreateInlineAction::class);

    $routes->add('admin_editor_block_edit', '/admin/editor/block/{id}/edit')
        ->methods(['GET', 'POST'])
        ->controller(BlockEditAction::class);

    $routes->add('admin_editor_block_add', '/admin/editor/draft/{draftId}/block/add')
        ->methods(['GET', 'POST'])
        ->controller(BlockAddAction::class);

    $routes->add('admin_editor_block_move', '/admin/editor/block/{id}/move/{position}')
        ->methods(['POST'])
        ->controller(BlockMoveAction::class);

    $routes->add('admin_editor_block_delete', '/admin/editor/block/{id}/delete')
        ->methods(['DELETE', 'POST'])
        ->controller(BlockDeleteAction::class);

    $routes->add('admin_editor_block_picker', '/admin/editor/draft/{draftId}/block/picker')
        ->methods(['GET'])
        ->controller(BlockPickerAction::class);
};
