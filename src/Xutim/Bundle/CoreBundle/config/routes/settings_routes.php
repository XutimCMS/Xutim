<?php

declare(strict_types=1);

use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;
use Xutim\CoreBundle\Action\Admin\Settings\ChangeLanguageContextAction;
use Xutim\CoreBundle\Action\Admin\Settings\EditSiteSettingsAction;
use Xutim\CoreBundle\Action\Admin\Settings\RedirectWithLanguageContextAction;

return function (RoutingConfigurator $routes) {
    $routes
        ->add('admin_settings_change_language_content_context', '/admin/{_content_locale}/settings/change-language-context/{locale}')
        ->methods(['get'])
        ->controller(ChangeLanguageContextAction::class)
    ;

    $routes
        ->add('admin_settings_site', '/admin/{_content_locale}/settings/site')
        ->methods(['get', 'post'])
        ->controller(EditSiteSettingsAction::class)
    ;

    $routes
        ->add('admin_settings_redirect_with_language_context', '/settings/redirect-with-language-context/{locale}')
        ->methods(['get'])
        ->controller(RedirectWithLanguageContextAction::class)
    ;
};
