<?php

declare(strict_types=1);

use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;
use Xutim\NotificationBundle\Action\Admin\Notification\ListNotificationsAction;
use Xutim\NotificationBundle\Action\Admin\Notification\MarkAllNotificationsReadAction;
use Xutim\NotificationBundle\Action\Admin\Notification\MarkNotificationReadAction;

return function (RoutingConfigurator $routes) {
    $routes
        ->add('admin_notification_list', '/admin/{_content_locale}/notifications')
        ->methods(['get'])
        ->controller(ListNotificationsAction::class)
    ;

    $routes
        ->add('admin_notification_read', '/admin/{_content_locale}/notifications/{id}/read')
        ->methods(['post'])
        ->controller(MarkNotificationReadAction::class)
    ;

    $routes
        ->add('admin_notification_read_all', '/admin/{_content_locale}/notifications/read-all')
        ->methods(['post'])
        ->controller(MarkAllNotificationsReadAction::class)
    ;
};
