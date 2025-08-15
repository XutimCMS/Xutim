<?php

declare(strict_types=1);

use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;

// use Symfony\Component\Routing\Requirement\EnumRequirement;
// use Xutim\CoreBundle\Entity\PublicationStatus;
// use Xutim\EventBundle\Action\Admin\CreateEventAction;
// use Xutim\EventBundle\Action\Admin\DeleteEventAction;
// use Xutim\EventBundle\Action\Admin\EditEventAction;
// use Xutim\EventBundle\Action\Admin\EditEventArticleAction;
// use Xutim\EventBundle\Action\Admin\EditEventDatesAction;
// use Xutim\EventBundle\Action\Admin\EditEventPageAction;
// use Xutim\EventBundle\Action\Admin\EditEventStatusAction;
// use Xutim\EventBundle\Action\Admin\ListEventsAction;

return function (RoutingConfigurator $routes) {
    // $routes->add('admin_event_new', '/event/new/{id?null}')
    //     ->methods(['get', 'post'])
    //     ->controller(CreateEventAction::class);
    //
    // $routes->add('admin_event_delete', '/event/delete/{id}')
    //     ->controller(DeleteEventAction::class);
    //
    // $routes->add('admin_event_edit', '/event/edit/{id}/{locale? }')
    //     ->methods(['get', 'post'])
    //     ->controller(EditEventAction::class);
    //
    // $routes->add('admin_event_article_edit', '/event/edit-article/{id}')
    //     ->methods(['get', 'post'])
    //     ->controller(EditEventArticleAction::class);
    //
    // $routes->add('admin_event_dates_edit', '/event/edit-dates/{id}')
    //     ->methods(['get', 'post'])
    //     ->controller(EditEventDatesAction::class);
    //
    // $routes->add('admin_event_page_edit', '/event/edit-page/{id}')
    //     ->methods(['get', 'post'])
    //     ->controller(EditEventPageAction::class);
    //
    // $routes->add('admin_event_list', '/admin/event')
    //     ->methods(['get'])
    //     ->controller(ListEventsAction::class);
    //
    // $routes->add('admin_event_publication_status_edit', '/publication-status/event/edit/{id}/{status}')
    //     ->methods(['post'])
    //     ->requirements(['status' => new EnumRequirement(PublicationStatus::class)])
    //     ->controller(EditEventStatusAction::class);
};
