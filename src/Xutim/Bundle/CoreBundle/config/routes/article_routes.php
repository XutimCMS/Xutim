<?php

declare(strict_types=1);

use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;
use Xutim\CoreBundle\Action\Admin\Article\CreateArticleAction;
use Xutim\CoreBundle\Action\Admin\Article\EditArticleAction;
use Xutim\CoreBundle\Action\Admin\Article\EditArticleLayoutAction;
use Xutim\CoreBundle\Action\Admin\Article\EditFeaturedImageAction;
use Xutim\CoreBundle\Action\Admin\Article\EditPublishedDateAction;
use Xutim\CoreBundle\Action\Admin\Article\EditScheduledPublishedDateAction;
use Xutim\CoreBundle\Action\Admin\Article\JsonEditorJSDataAction;
use Xutim\CoreBundle\Action\Admin\Article\JsonListArticlesAction;
use Xutim\CoreBundle\Action\Admin\Article\ListArticlesAction;
use Xutim\CoreBundle\Action\Admin\Article\MarkDefaultTranslationAction;
use Xutim\CoreBundle\Action\Admin\Article\ShowArticleAction;
use Xutim\CoreBundle\Action\Admin\Article\ShowArticleBySlugAction;
use Xutim\CoreBundle\Action\Admin\Article\ShowArticlePreviewAction;
use Xutim\CoreBundle\Action\Admin\Article\ToggleTagAction;

return function (RoutingConfigurator $routes) {
    $routes
        ->add('admin_article_new', '/admin/{_content_locale}/article/new')
        ->methods(['get', 'post'])
        ->controller(CreateArticleAction::class)
    ;

    $routes
        ->add('admin_article_edit', '/admin/{_content_locale}/article/edit/{id}/{locale? }')
        ->methods(['get', 'post'])
        ->controller(EditArticleAction::class)
    ;

    $routes
        ->add('admin_article_layout_edit', '/admin/{_content_locale}/article/layout-edit/{id}')
        ->methods(['get', 'post'])
        ->controller(EditArticleLayoutAction::class)
    ;

    $routes
        ->add('admin_article_featured_image_edit', '/admin/{_content_locale}/article/edit-featured-image/{id?null}')
        ->methods(['get', 'post'])
        ->controller(EditFeaturedImageAction::class)
    ;

    $routes
        ->add('admin_article_edit_publication_date', '/admin/{_content_locale}/article/edit-publication-date/{id}')
        ->methods(['get', 'post'])
        ->controller(EditPublishedDateAction::class)
    ;

    $routes
        ->add('admin_article_edit_scheduled_publication_date', '/admin/{_content_locale}/article/edit-scheduled-publication-date/{id}')
        ->methods(['get', 'post'])
        ->controller(EditScheduledPublishedDateAction::class)
    ;

    $routes
        ->add('admin_json_article_list', '/admin/{_content_locale}/json/article/list')
        ->methods(['get'])
        ->controller(JsonListArticlesAction::class)
    ;

    $routes
        ->add('admin_json_content_translation_show', '/admin/{_content_locale}/json/content-translation/{id}/show')
        ->methods(['get'])
        ->controller(JsonEditorJSDataAction::class)
    ;

    $routes
        ->add('admin_article_list', '/admin/{_content_locale}/article')
        ->methods(['get'])
        ->controller(ListArticlesAction::class)
    ;

    $routes
        ->add('admin_article_mark_default_translation', '/admin/{_content_locale}/article/{id}/mark-default-translation/{transId}')
        ->methods(['post'])
        ->controller(MarkDefaultTranslationAction::class)
    ;

    $routes
        ->add('admin_article_show', '/admin/{_content_locale}/article/{id<[^/]+>}')
        ->methods(['get'])
        ->controller(ShowArticleAction::class)
    ;

    $routes
        ->add('admin_article_show_by_slug', '/admin/{_content_locale}/article/by-slug/{slug}')
        ->methods(['get'])
        ->controller(ShowArticleBySlugAction::class)
    ;

    $routes
        ->add('admin_article_frame_show', '/admin/{_content_locale}/article-frame/{id<[^/]+>}')
        ->methods(['get'])
        ->controller(ShowArticlePreviewAction::class)
    ;

    $routes
        ->add('admin_article_toggle_tag', '/admin/{_content_locale}/article/toggle-tag/{id}/{tagId}')
        ->methods(['get', 'post'])
        ->controller(ToggleTagAction::class)
    ;
};
