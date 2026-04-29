<?php

declare(strict_types=1);

use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;
use Xutim\RedirectBundle\Action\Admin\CreateRedirectAction;
use Xutim\RedirectBundle\Action\Admin\DeleteRedirectAction;
use Xutim\RedirectBundle\Action\Admin\EditRedirectAction;
use Xutim\RedirectBundle\Action\Admin\ListRedirectsAction;

return function (RoutingConfigurator $routes) {
    $routes->add('admin_redirect_new', '/admin/redirect/new')
        ->methods(['get', 'post'])
        ->controller(CreateRedirectAction::class);

    $routes->add('admin_redirect_delete', '/admin/redirect/delete/{id}')
        ->controller(DeleteRedirectAction::class);

    $routes->add('admin_redirect_edit', '/admin/redirect/edit/{id}')
        ->methods(['get', 'post'])
        ->controller(EditRedirectAction::class);

    $routes->add('admin_redirect_list', '/admin/redirect')
        ->methods(['get'])
        ->controller(ListRedirectsAction::class);
};
