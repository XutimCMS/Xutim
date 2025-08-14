<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Xutim\AnalyticsBundle\Domain\Factory\AnalyticsEventFactoryInterface;
use Xutim\AnalyticsBundle\Domain\Repository\AnalyticsEventRepositoryInterface;
use Xutim\AnalyticsBundle\Message\CollectAnalyticsMessage;
use Xutim\AnalyticsBundle\MessageHandler\CollectAnalyticsMessageHandler;

return static function (ContainerConfigurator $container): void {
    $services = $container->services();

    $services->set(CollectAnalyticsMessageHandler::class)
        ->arg('$factory', service(AnalyticsEventFactoryInterface::class))
        ->arg('$repo', service(AnalyticsEventRepositoryInterface::class))
        ->arg('$appSecret', param('kernel.secret'))
        ->tag('messenger.message_handler', [
            'handles' => CollectAnalyticsMessage::class,
            'bus' => 'command.bus'
        ])
    ;
};
