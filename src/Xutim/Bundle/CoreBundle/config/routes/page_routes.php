<?php

declare(strict_types=1);

use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;
use Xutim\CoreBundle\Action\Admin\Page\CreatePageAction;
use Xutim\CoreBundle\Action\Admin\Page\EditFeaturedImageAction;
use Xutim\CoreBundle\Action\Admin\Page\EditPageAction;
use Xutim\CoreBundle\Action\Admin\Page\EditPageDetailsAction;
use Xutim\CoreBundle\Action\Admin\Page\EditPageLayoutAction;
use Xutim\CoreBundle\Action\Admin\Page\JsonListPagesAction;
use Xutim\CoreBundle\Action\Admin\Page\JsonReorderPagesAction;
use Xutim\CoreBundle\Action\Admin\Page\ListPagesAction;
use Xutim\CoreBundle\Action\Admin\Page\MarkDefaultTranslationAction;
use Xutim\CoreBundle\Action\Admin\Page\MovePagePositionAction;
use Xutim\CoreBundle\Action\Admin\Page\ShowPageBySlugAction;
use Xutim\CoreBundle\Action\Admin\Page\ShowPagePreviewAction;
use Xutim\CoreBundle\Action\Admin\Page\ShowPageTranslationAction;

return function (RoutingConfigurator $routes) {
    $routes
        ->add('admin_page_new', '/admin/{_content_locale}/page/new/{id?}')
        ->methods(['get', 'post'])
        ->controller(CreatePageAction::class)
    ;

    $routes
        ->add('admin_page_featured_image_edit', '/admin/{_content_locale}/page/edit-featured-image/{id}')
        ->methods(['get', 'post'])
        ->controller(EditFeaturedImageAction::class)
    ;

    $routes
        ->add('admin_page_edit', '/admin/{_content_locale}/page/edit/{id}/{locale? }')
        ->methods(['get', 'post'])
        ->controller(EditPageAction::class)
    ;

    $routes
        ->add('admin_page_details_edit', '/admin/{_content_locale}/page/details-edit/{id}')
        ->methods(['get', 'post'])
        ->controller(EditPageDetailsAction::class)
    ;

    $routes
        ->add('admin_page_layout_edit', '/admin/{_content_locale}/page/layout-edit/{id}')
        ->methods(['get', 'post'])
        ->controller(EditPageLayoutAction::class)
    ;

    $routes
        ->add('admin_json_page_list', '/admin/{_content_locale}/json/page/list')
        ->methods(['get'])
        ->controller(JsonListPagesAction::class)
    ;

    $routes
        ->add('admin_json_page_move', '/admin/{_content_locale}/json/page/move/{id}/{direction}')
        ->methods(['post'])
        ->requirements(['direction' => '0|1'])
        ->controller(JsonReorderPagesAction::class)
    ;

    $routes
        ->add('admin_page_list', '/admin/{_content_locale}/page-list/{id?}')
        ->methods(['get'])
        ->controller(ListPagesAction::class)
    ;

    $routes
        ->add('admin_page_mark_default_translation', '/admin/{_content_locale}/page/{id}/mark-default-translation/{transId}')
        ->methods(['get', 'post'])
        ->controller(MarkDefaultTranslationAction::class)
    ;

    $routes
        ->add('admin_page_move', '/admin/{_content_locale}/page/{id}/move/{dir}')
        ->methods(['get', 'post'])
        ->controller(MovePagePositionAction::class)
    ;

    $routes
        ->add('admin_page_show_by_slug', '/admin/{_content_locale}/page/by-slug/{slug}')
        ->methods(['get'])
        ->controller(ShowPageBySlugAction::class)
    ;

    $routes
        ->add('admin_page_frame_show', '/admin/{_content_locale}/page-frame/{id<[^/]+>}')
        ->methods(['get'])
        ->controller(ShowPagePreviewAction::class)
    ;

    $routes
        ->add('admin_page_translation_show', '/admin/{_content_locale}/page/{id}/show-translation/{locale}')
        ->methods(['get'])
        ->controller(ShowPageTranslationAction::class)
    ;
};
