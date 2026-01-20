<?php

declare(strict_types=1);

namespace Xutim\AnalyticsBundle\MessageHandler;

use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Xutim\AnalyticsBundle\Message\AggregateAnalyticsMessage;
use Xutim\AnalyticsBundle\Service\AnalyticsAggregationService;

#[AsMessageHandler]
final readonly class AggregateAnalyticsMessageHandler
{
    public function __construct(
        private AnalyticsAggregationService $aggregationService,
        private ?LoggerInterface $logger = null,
    ) {
    }

    public function __invoke(AggregateAnalyticsMessage $message): void
    {
        $date = $message->date;

        $eventCount = $this->aggregationService->countEventsForDate($date);

        if ($eventCount === 0) {
            $this->logger?->info('No analytics events to aggregate for {date}', [
                'date' => $date->format('Y-m-d'),
            ]);
            return;
        }

        $results = $this->aggregationService->aggregateAll($date);

        $this->logger?->info('Aggregated analytics for {date}: {results}', [
            'date' => $date->format('Y-m-d'),
            'event_count' => $eventCount,
            'results' => $results,
        ]);
    }
}
