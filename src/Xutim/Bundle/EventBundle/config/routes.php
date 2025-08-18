<?php

declare(strict_types=1);

use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;
use Symfony\Component\Routing\Requirement\EnumRequirement;
use Xutim\CoreBundle\Entity\PublicationStatus;
use Xutim\EventBundle\Action\Admin\CreateEventAction;
use Xutim\EventBundle\Action\Admin\DeleteEventAction;
use Xutim\EventBundle\Action\Admin\EditEventAction;
use Xutim\EventBundle\Action\Admin\EditEventArticleAction;
use Xutim\EventBundle\Action\Admin\EditEventDatesAction;
use Xutim\EventBundle\Action\Admin\EditEventPageAction;
use Xutim\EventBundle\Action\Admin\EditEventStatusAction;
use Xutim\EventBundle\Action\Admin\ListEventsAction;

return function (RoutingConfigurator $routes) {
    $requirements = ['_content_locale' => '[a-z]{2}(?:_[A-Za-z]{2,8})*'];
    $defaults = ['_content_locale' => '%kernel.default_locale%'];

    $routes->add('admin_event_new', '/admin/{_content_locale}/event/new/{id?null}')
        ->methods(['get', 'post'])
        ->controller(CreateEventAction::class)

        ->requirements($requirements)
        ->defaults($defaults)
    ;

    $routes->add('admin_event_delete', '/admin/{_content_locale}/event/delete/{id}')
        ->controller(DeleteEventAction::class)
        ->requirements($requirements)
        ->defaults($defaults)
    ;

    $routes->add('admin_event_edit', '/admin/{_content_locale}/event/edit/{id}/{locale? }')
        ->methods(['get', 'post'])
        ->controller(EditEventAction::class)
        ->requirements($requirements)
        ->defaults($defaults)
    ;

    $routes->add('admin_event_article_edit', '/admin/{_content_locale}/event/edit-article/{id}')
        ->methods(['get', 'post'])
        ->controller(EditEventArticleAction::class)
        ->requirements($requirements)
        ->defaults($defaults)
    ;

    $routes->add('admin_event_dates_edit', '/admin/{_content_locale}/event/edit-dates/{id}')
        ->methods(['get', 'post'])
        ->controller(EditEventDatesAction::class)
        ->requirements($requirements)
        ->defaults($defaults)
    ;

    $routes->add('admin_event_page_edit', '/admin/{_content_locale}/event/edit-page/{id}')
        ->methods(['get', 'post'])
        ->controller(EditEventPageAction::class)
        ->requirements($requirements)
        ->defaults($defaults)
    ;

    $routes->add('admin_event_list', '/admin/{_content_locale}/event')
        ->methods(['get'])
        ->controller(ListEventsAction::class)
        ->requirements($requirements)
        ->defaults($defaults)
    ;

    $routes->add('admin_event_publication_status_edit', '/admin/{_content_locale}/publication-status/event/edit/{id}/{status}')
        ->methods(['post'])
        ->controller(EditEventStatusAction::class)
        ->requirements(array_merge($requirements, ['status' => new EnumRequirement(PublicationStatus::class)]))
        ->defaults($defaults)
    ;
};
