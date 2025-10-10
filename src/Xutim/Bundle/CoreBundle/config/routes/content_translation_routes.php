<?php

declare(strict_types=1);

use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;
use Xutim\CoreBundle\Action\Admin\ContentTranslation\DeleteTranslationAction;
use Xutim\CoreBundle\Action\Admin\ContentTranslation\EditTranslationAction;
use Xutim\CoreBundle\Action\Admin\ContentTranslation\JsonGenerateSlugAction;
use Xutim\CoreBundle\Action\Admin\ContentTranslation\ShowTranslationRevisionsAction;

return function (RoutingConfigurator $routes) {
    $routes
        ->add('admin_content_translation_delete', '/admin/{_content_locale}/content-translation/delete/{id}')
        ->methods(['get', 'post'])
        ->controller(DeleteTranslationAction::class)
    ;

    $routes
        ->add('admin_content_translation_edit', '/admin/{_content_locale}/content-translation/edit/{id}')
        ->methods(['get', 'post'])
        ->controller(EditTranslationAction::class)
    ;

    $routes
        ->add('admin_json_content_translation_generate_slug', '/admin/{_content_locale}/json/content-translation/generate-slug')
        ->methods(['get'])
        ->controller(JsonGenerateSlugAction::class)
    ;

    $routes
        ->add('admin_content_translation_revisions', '/admin/{_content_locale}/content-translation/revisions/{id}/{oldId?}/{newId?}')
        ->methods(['get'])
        ->controller(ShowTranslationRevisionsAction::class)
        ->requirements([
            'oldId' => '[0-9a-fA-F-]{36}',
            'newId' => '[0-9a-fA-F-]{36}'
        ])
    ;
};
