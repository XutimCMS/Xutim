<?php

declare(strict_types=1);

use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;
use Xutim\SnippetBundle\Action\CreateSnippetAction;
use Xutim\SnippetBundle\Action\DeleteSnippetAction;
use Xutim\SnippetBundle\Action\EditSnippetAction;
use Xutim\SnippetBundle\Action\JsonListSnippetsAction;
use Xutim\SnippetBundle\Action\ListSnippetsAction;
use Xutim\SnippetBundle\Action\TranslateSnippetAction;

return function (RoutingConfigurator $routes) {
    $routes->add('admin_snippet_new', '/admin/snippet/new')
        ->methods(['get', 'post'])
        ->controller(CreateSnippetAction::class);

    $routes->add('admin_snippet_delete', '/admin/snippet/delete/{id}')
        ->methods(['post'])
        ->controller(DeleteSnippetAction::class);

    $routes->add('admin_snippet_edit', '/admin/snippet/edit/{id}/{locale? }') ->methods(['get', 'post'])
        ->controller(EditSnippetAction::class);

    $routes->add('admin_json_snippet_list', '/json/snippet/list/{type}')
        ->methods(['get'])
        ->controller(JsonListSnippetsAction::class);

    $routes->add('admin_snippet_list', '/admin/snippet')
        ->methods(['get'])
        ->controller(ListSnippetsAction::class);

    $routes->add('admin_json_snippet_translate', '/admin/snippet/translate')
        ->methods(['post'])
        ->controller(TranslateSnippetAction::class);
};
