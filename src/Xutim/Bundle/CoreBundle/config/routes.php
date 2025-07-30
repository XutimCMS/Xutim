<?php

declare(strict_types=1);

use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;

return function (RoutingConfigurator $routes) {
    $routes->import('.', 'snippet_routes');
    $requirements = ['_content_locale' => '[a-z]{2}(?:_[A-Za-z]{2,8})*'];
    $defaults = ['_content_locale' => '%kernel.default_locale%'];

    $routes->import('routes/article_routes.php')
        ->requirements($requirements)
        ->defaults($defaults)
    ;

    $routes->import('routes/block_routes.php')
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

    $routes->import('routes/public_routes.php');

    $routes->import('.', 'content_translation_fallback');
};
