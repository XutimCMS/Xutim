<?php

declare(strict_types=1);

use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;
use Xutim\CoreBundle\Action\Public\ShowContentTranslation;

return function (RoutingConfigurator $routes) {
    $localePattern = '[a-z]{2}(?:_[A-Za-z]{2,8})*';
    $requirements = ['_content_locale' => $localePattern];
    $defaults = ['_content_locale' => '%kernel.default_locale%'];

    $routes->import('routes/article_routes.php')
        ->requirements($requirements)
        ->defaults($defaults)
    ;

    $routes->import('routes/block_routes.php')
        ->requirements($requirements)
        ->defaults($defaults)
    ;

    $routes->import('routes/content_draft_routes.php')
        ->requirements($requirements)
        ->defaults($defaults)
    ;

    $routes->import('routes/content_translation_routes.php')
        ->requirements($requirements)
        ->defaults($defaults)
    ;

    $routes->import('routes/general_routes.php')
        ->requirements($requirements)
        ->defaults($defaults)
    ;

    $routes->import('routes/media_routes.php')
        ->requirements($requirements)
        ->defaults($defaults)
    ;

    $routes->import('routes/menu_routes.php')
        ->requirements($requirements)
        ->defaults($defaults)
    ;

    $routes->import('routes/notification_routes.php')
        ->requirements($requirements)
        ->defaults($defaults)
    ;

    $routes->import('routes/page_routes.php')
        ->requirements($requirements)
        ->defaults($defaults)
    ;

    $routes->import('routes/publication_status_routes.php')
        ->requirements($requirements)
        ->defaults($defaults)
    ;

    $routes->import('routes/settings_routes.php')
        ->requirements($requirements)
        ->defaults($defaults)
    ;

    $routes->import('routes/tag_routes.php')
        ->requirements($requirements)
        ->defaults($defaults)
    ;

    $routes->import('routes/xutim_layout_routes.php')
        ->requirements($requirements)
        ->defaults($defaults)
    ;

    $routes->import('routes/public_routes.php');

    $routes->add('content_translation_show', '/{_locale}/{slug}/{_content_locale}')
        ->controller(ShowContentTranslation::class)
        ->defaults(['_content_locale' => null])
        ->requirements([
            'slug' => '[a-zA-Z0-9\-]+',
            '_locale' => $localePattern,
            '_content_locale' => $localePattern,
        ]);
};
