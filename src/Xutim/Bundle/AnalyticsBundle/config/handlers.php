<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Xutim\AnalyticsBundle\Domain\Factory\AnalyticsEventFactoryInterface;
use Xutim\AnalyticsBundle\Domain\Repository\AnalyticsEventArchiveRepositoryInterface;
use Xutim\AnalyticsBundle\Domain\Repository\AnalyticsEventRepositoryInterface;
use Xutim\AnalyticsBundle\Message\AggregateAnalyticsMessage;
use Xutim\AnalyticsBundle\Message\ArchiveAnalyticsMessage;
use Xutim\AnalyticsBundle\Message\CollectAnalyticsMessage;
use Xutim\AnalyticsBundle\MessageHandler\AggregateAnalyticsMessageHandler;
use Xutim\AnalyticsBundle\MessageHandler\ArchiveAnalyticsMessageHandler;
use Xutim\AnalyticsBundle\MessageHandler\CollectAnalyticsMessageHandler;
use Xutim\AnalyticsBundle\Service\AnalyticsAggregationService;

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

    $services->set(AggregateAnalyticsMessageHandler::class)
        ->arg('$aggregationService', service(AnalyticsAggregationService::class))
        ->arg('$logger', service(LoggerInterface::class)->nullOnInvalid())
        ->tag('messenger.message_handler', [
            'handles' => AggregateAnalyticsMessage::class,
        ])
    ;

    $services->set(ArchiveAnalyticsMessageHandler::class)
        ->arg('$em', service(EntityManagerInterface::class))
        ->arg('$eventRepository', service(AnalyticsEventRepositoryInterface::class))
        ->arg('$archiveRepository', service(AnalyticsEventArchiveRepositoryInterface::class))
        ->arg('$logger', service(LoggerInterface::class)->nullOnInvalid())
        ->tag('messenger.message_handler', [
            'handles' => ArchiveAnalyticsMessage::class,
        ])
    ;
};
