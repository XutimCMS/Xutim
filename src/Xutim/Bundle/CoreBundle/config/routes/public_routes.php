<?php

declare(strict_types=1);

use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;
use Xutim\CoreBundle\Action\Public\HomepageAction;
use Xutim\CoreBundle\Action\Public\HomepageRedirectAction;
use Xutim\CoreBundle\Action\Public\ShowFileAction;
use Xutim\CoreBundle\Action\Public\ShowTagTranslation;

return function (RoutingConfigurator $routes) {
    $routes
        ->add('homepage', '/{_locale?en}/')
        ->methods(['get'])
        ->requirements(['_locale' => '[a-z]{2}(?:_[A-Z]{2})?'])
        ->controller(HomepageAction::class)
    ;

    $routes
        ->add('homepage_redirect', '/')
        ->methods(['get'])
        ->controller(HomepageRedirectAction::class)

    ;

    $routes
        ->add('file_show', '/file/show/{id}.{extension}')
        ->methods(['get'])
        ->controller(ShowFileAction::class)
    ;

    $routes
        ->add('tag_translation_show', '/{_locale}/tag/{slug<[a-zA-Z0-9\-]+>}/{page<\d+>?1}')
        ->methods(['get'])
        ->controller(ShowTagTranslation::class)
    ;
};
