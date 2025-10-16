<?php

declare(strict_types=1);

use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;
use Xutim\CoreBundle\Action\Admin\Media\CreateFileAction;
use Xutim\CoreBundle\Action\Admin\Media\CreateMediaFolderAction;
use Xutim\CoreBundle\Action\Admin\Media\DeleteFileAction;
use Xutim\CoreBundle\Action\Admin\Media\EditCopyrightAction;
use Xutim\CoreBundle\Action\Admin\Media\EditFileAction;
use Xutim\CoreBundle\Action\Admin\Media\EditMediaFolderAction;
use Xutim\CoreBundle\Action\Admin\Media\JsonListAllFilesAction;
use Xutim\CoreBundle\Action\Admin\Media\JsonListFilesAction;
use Xutim\CoreBundle\Action\Admin\Media\JsonListImagesAction;
use Xutim\CoreBundle\Action\Admin\Media\JsonShowFileAction;
use Xutim\CoreBundle\Action\Admin\Media\ListFilesAction;
use Xutim\CoreBundle\Action\Admin\Media\MoveFileToFolderAction;
use Xutim\CoreBundle\Action\Public\ShowFileAction;

return function (RoutingConfigurator $routes) {
    $routes
        ->add('admin_media_folder_new', '/admin/{_content_locale}/media/folder/new/{id?}')
        ->methods(['get', 'post'])
        ->controller(CreateMediaFolderAction::class)
    ;

    $routes
        ->add('admin_media_folder_edit', '/admin/{_content_locale}/media/folder/edit/{id}')
        ->methods(['get', 'post'])
        ->controller(EditMediaFolderAction::class)
    ;

    $routes
        ->add('admin_media_move_file_to_folder', '/admin/{_content_locale}/media/move-file-to-folder')
        ->methods(['post'])
        ->controller(MoveFileToFolderAction::class)
    ;

    $routes
        ->add('admin_media_new', '/admin/{_content_locale}/media/new/{id?}')
        ->methods(['get', 'post'])
        ->controller(CreateFileAction::class)
    ;

    $routes
        ->add('admin_media_delete', '/admin/{_content_locale}/media/delete/{id}')
        ->methods(['get', 'post'])
        ->controller(DeleteFileAction::class)
    ;

    $routes
        ->add('admin_media_copyright_edit', '/admin/{_content_locale}/media/copyright-edit/{id}')
        ->methods(['get', 'post'])
        ->controller(EditCopyrightAction::class)
    ;

    $routes
        ->add('admin_media_edit', '/admin/{_content_locale}/media/edit/{id}')
        ->methods(['get', 'post'])
        ->controller(EditFileAction::class)
    ;

    $routes
        ->add('admin_json_file_all_list', '/admin/{_content_locale}/json/file/all-list')
        ->methods(['get'])
        ->controller(JsonListAllFilesAction::class)
    ;

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

    $routes
        ->add('admin_media_list', '/admin/{_content_locale}/media/{id?}')
        ->methods(['get'])
        ->controller(ListFilesAction::class)
    ;

    $routes
        ->add('admin_media_translation_show', '/admin/{_content_locale}/media/show-translation/{id}')
        ->methods(['get'])
        ->controller(ShowFileAction::class)
    ;
};
