<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Doctrine\ORM\EntityManagerInterface;
use UAParser\Parser;
use Xutim\AnalyticsBundle\Command\AggregateAnalyticsCommand;
use Xutim\AnalyticsBundle\Command\ArchiveAnalyticsCommand;
use Xutim\AnalyticsBundle\Command\BackfillAggregatesCommand;
use Xutim\AnalyticsBundle\Domain\Repository\AnalyticsDailyCountryRepositoryInterface;
use Xutim\AnalyticsBundle\Domain\Repository\AnalyticsDailyDeviceRepositoryInterface;
use Xutim\AnalyticsBundle\Domain\Repository\AnalyticsDailyPageReferrerRepositoryInterface;
use Xutim\AnalyticsBundle\Domain\Repository\AnalyticsDailySessionRepositoryInterface;
use Xutim\AnalyticsBundle\Domain\Repository\AnalyticsDailySummaryRepositoryInterface;
use Xutim\AnalyticsBundle\Domain\Repository\AnalyticsDailyTrafficSourceRepositoryInterface;
use Xutim\AnalyticsBundle\Domain\Repository\AnalyticsDailyUtmRepositoryInterface;
use Xutim\AnalyticsBundle\Domain\Repository\AnalyticsEventArchiveRepositoryInterface;
use Xutim\AnalyticsBundle\Domain\Repository\AnalyticsEventRepositoryInterface;
use Xutim\AnalyticsBundle\Service\AnalyticsAggregationService;
use Xutim\AnalyticsBundle\Service\DateRangeResolver;
use Xutim\AnalyticsBundle\Service\ReferrerParser;
use Xutim\AnalyticsBundle\Service\UserAgentParser;

return static function (ContainerConfigurator $container): void {
    $services = $container->services();

    $services->set(Parser::class)
        ->factory([Parser::class, 'create'])
    ;

    $services->set(UserAgentParser::class)
        ->arg('$parser', service(Parser::class))
    ;

    $services->set(DateRangeResolver::class);

    $services->set(ReferrerParser::class);

    $services->set(AnalyticsAggregationService::class)
        ->arg('$eventRepository', service(AnalyticsEventRepositoryInterface::class))
        ->arg('$summaryRepository', service(AnalyticsDailySummaryRepositoryInterface::class))
        ->arg('$pageReferrerRepository', service(AnalyticsDailyPageReferrerRepositoryInterface::class))
        ->arg('$trafficSourceRepository', service(AnalyticsDailyTrafficSourceRepositoryInterface::class))
        ->arg('$countryRepository', service(AnalyticsDailyCountryRepositoryInterface::class))
        ->arg('$deviceRepository', service(AnalyticsDailyDeviceRepositoryInterface::class))
        ->arg('$utmRepository', service(AnalyticsDailyUtmRepositoryInterface::class))
        ->arg('$sessionRepository', service(AnalyticsDailySessionRepositoryInterface::class))
        ->arg('$userAgentParser', service(UserAgentParser::class))
        ->arg('$referrerParser', service(ReferrerParser::class))
        ->arg('$siteHost', '%xutim_analytics.site_host%')
    ;

    $services->set(AggregateAnalyticsCommand::class)
        ->arg('$aggregationService', service(AnalyticsAggregationService::class))
        ->tag('console.command')
    ;

    $services->set(BackfillAggregatesCommand::class)
        ->arg('$aggregationService', service(AnalyticsAggregationService::class))
        ->arg('$eventRepository', service(AnalyticsEventRepositoryInterface::class))
        ->tag('console.command')
    ;

    $services->set(ArchiveAnalyticsCommand::class)
        ->arg('$em', service(EntityManagerInterface::class))
        ->arg('$eventRepository', service(AnalyticsEventRepositoryInterface::class))
        ->arg('$archiveRepository', service(AnalyticsEventArchiveRepositoryInterface::class))
        ->tag('console.command')
    ;
};
