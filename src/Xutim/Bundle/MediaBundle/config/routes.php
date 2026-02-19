<?php

declare(strict_types=1);

use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;
use Xutim\MediaBundle\Action\Admin\EditCopyrightAction;
use Xutim\MediaBundle\Action\Admin\JsonListAllFilesAction;
use Xutim\MediaBundle\Action\Admin\ListMediaAction;
use Xutim\MediaBundle\Action\Admin\CreateFolderAction;
use Xutim\MediaBundle\Action\Admin\DeleteMediaAction;
use Xutim\MediaBundle\Action\Admin\DeleteFolderAction;
use Xutim\MediaBundle\Action\Admin\EditFolderAction;
use Xutim\MediaBundle\Action\Admin\EditMediaAction;
use Xutim\MediaBundle\Action\Admin\MoveMediaAction;
use Xutim\MediaBundle\Action\Admin\MoveMediaToFolderAction;
use Xutim\MediaBundle\Action\Admin\PresetPreviewAction;
use Xutim\MediaBundle\Action\Admin\RegenerateVariantsAction;
use Xutim\MediaBundle\Action\Admin\ShowMediaTranslationAction;
use Xutim\MediaBundle\Action\Admin\EditFocalPointAction;
use Xutim\MediaBundle\Action\Admin\UpdateFocalPointAction;
use Xutim\MediaBundle\Action\Admin\UploadMediaAction;

return function (RoutingConfigurator $routes) {
    $routes->add('admin_media_focal_point', '/media/{id}/focal-point')
        ->methods(['POST'])
        ->controller(UpdateFocalPointAction::class);

    $routes->add('admin_media_focal_point_edit', '/media/{id}/focal-point/edit')
        ->methods(['GET'])
        ->controller(EditFocalPointAction::class);

    $routes->add('admin_media_regenerate_variants', '/media/{id}/regenerate-variants')
        ->methods(['POST'])
        ->controller(RegenerateVariantsAction::class);

    $routes->add('admin_media_preset_preview', '/media/{id}/presets')
        ->methods(['GET'])
        ->controller(PresetPreviewAction::class);

    $routes->add('admin_media_edit', '/media/{id}/edit')
        ->methods(['GET', 'POST'])
        ->controller(EditMediaAction::class);

    $routes->add('admin_media_upload', '/media/upload/{id?}')
        ->methods(['GET', 'POST'])
        ->controller(UploadMediaAction::class);

    $routes->add('admin_media_delete', '/media/{id}/delete')
        ->methods(['GET', 'POST'])
        ->controller(DeleteMediaAction::class);

    $routes->add('admin_media_folder_new', '/media/folder/new/{id?}')
        ->methods(['GET', 'POST'])
        ->controller(CreateFolderAction::class);

    $routes->add('admin_media_folder_edit', '/media/folder/{id}/edit')
        ->methods(['GET', 'POST'])
        ->controller(EditFolderAction::class);

    $routes->add('admin_media_folder_delete', '/media/folder/{id}/delete')
        ->methods(['GET', 'POST'])
        ->controller(DeleteFolderAction::class);

    $routes->add('admin_media_move', '/media/{id}/move/{folderId?}')
        ->methods(['GET', 'POST'])
        ->controller(MoveMediaAction::class);

    $routes->add('admin_media_move_file_to_folder', '/media/move-to-folder')
        ->methods(['POST'])
        ->controller(MoveMediaToFolderAction::class);

    $routes->add('admin_media_copyright_edit', '/media/{id}/copyright')
        ->methods(['GET', 'POST'])
        ->controller(EditCopyrightAction::class);

    $routes->add('admin_json_file_all_list', '/json/file/all-list')
        ->methods(['GET'])
        ->controller(JsonListAllFilesAction::class);

    $routes->add('admin_media_translation_show', '/media/show-translation/{id}')
        ->methods(['GET'])
        ->controller(ShowMediaTranslationAction::class);

    $routes->add('admin_media_list', '/media/{id?}')
        ->methods(['GET'])
        ->controller(ListMediaAction::class);
};
