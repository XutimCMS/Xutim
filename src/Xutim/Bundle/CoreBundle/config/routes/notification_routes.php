<?php

declare(strict_types=1);

use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;
use Xutim\CoreBundle\Action\Admin\Article\NotifyArticleTranslatorsAction;
use Xutim\CoreBundle\Action\Admin\Page\NotifyPageTranslatorsAction;

return function (RoutingConfigurator $routes) {
    $routes
        ->add('admin_article_notify_translators', '/admin/{_content_locale}/article/{id}/notify-translators')
        ->methods(['get', 'post'])
        ->controller(NotifyArticleTranslatorsAction::class)
    ;

    $routes
        ->add('admin_page_notify_translators', '/admin/{_content_locale}/page/{id}/notify-translators')
        ->methods(['get', 'post'])
        ->controller(NotifyPageTranslatorsAction::class)
    ;
};
