<?php

declare(strict_types=1);

use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;
use Xutim\CoreBundle\Action\Admin\Tag\CreateTagAction;
use Xutim\CoreBundle\Action\Admin\Tag\DeleteTagAction;
use Xutim\CoreBundle\Action\Admin\Tag\EditExcludeFromNewsAction;
use Xutim\CoreBundle\Action\Admin\Tag\EditFeaturedImageAction;
use Xutim\CoreBundle\Action\Admin\Tag\EditTagAction;
use Xutim\CoreBundle\Action\Admin\Tag\JsonGenerateSlugAction;
use Xutim\CoreBundle\Action\Admin\Tag\JsonListTagsAction;
use Xutim\CoreBundle\Action\Admin\Tag\ListTagsAction;

return function (RoutingConfigurator $routes) {
    $routes
        ->add('admin_tag_new', '/admin/{_content_locale}/tag/new')
        ->methods(['get', 'post'])
        ->controller(CreateTagAction::class)
    ;

    $routes
        ->add('admin_tag_delete', '/admin/{_content_locale}/tag/delete/{id}')
        ->methods(['get', 'post'])
        ->controller(DeleteTagAction::class)
    ;

    $routes
        ->add('admin_tag_exclude_from_news_toggle', '/tag/exclude-from-news-toggle/{id}')
        ->methods(['get', 'post'])
        ->controller(EditExcludeFromNewsAction::class)
    ;

    $routes
        ->add('admin_tag_featured_image_edit', '/admin/{_content_locale}/tag/edit-featured-image/{id?null}')
        ->methods(['get', 'post'])
        ->controller(EditFeaturedImageAction::class)
    ;

    $routes
        ->add('admin_tag_edit', '/admin/{_content_locale}/tag/edit/{id}/{locale? }')
        ->methods(['get', 'post'])
        ->controller(EditTagAction::class)
    ;

    $routes
        ->add('admin_json_tag_generate_slug', '/admin/{_content_locale}/json/tag/generate-slug')
        ->methods(['get'])
        ->controller(JsonGenerateSlugAction::class)
    ;


    $routes
        ->add('admin_json_tag_list', '/admin/{_content_locale}/json/tag/list')
        ->methods(['get'])
        ->controller(JsonListTagsAction::class)
    ;

    $routes
        ->add('admin_tag_list', '/admin/{_content_locale}/tag')
        ->methods(['get'])
        ->controller(ListTagsAction::class)
    ;
};
