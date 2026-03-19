<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Xutim\NotificationBundle\Service\NotificationCenterView;

return static function (ContainerConfigurator $container): void {
    $container->extension('twig', [
        'globals' => [
            'notification_center' => service(NotificationCenterView::class),
        ],
    ]);
};
