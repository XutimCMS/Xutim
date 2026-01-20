<?php

declare(strict_types=1);

namespace Xutim\AnalyticsBundle\MessageHandler;

use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Xutim\AnalyticsBundle\Domain\Repository\AnalyticsEventArchiveRepositoryInterface;
use Xutim\AnalyticsBundle\Domain\Repository\AnalyticsEventRepositoryInterface;
use Xutim\AnalyticsBundle\Message\ArchiveAnalyticsMessage;

#[AsMessageHandler]
final readonly class ArchiveAnalyticsMessageHandler
{
    public function __construct(
        private EntityManagerInterface $em,
        private AnalyticsEventRepositoryInterface $eventRepository,
        private AnalyticsEventArchiveRepositoryInterface $archiveRepository,
        private ?LoggerInterface $logger = null,
    ) {
    }

    public function __invoke(ArchiveAnalyticsMessage $message): void
    {
        $cutoffDate = (new \DateTimeImmutable())
            ->modify(sprintf('-%d days', $message->retentionDays))
            ->setTime(0, 0, 0);

        $countToArchive = $this->eventRepository->countEventsOlderThan($cutoffDate);

        if ($countToArchive === 0) {
            $this->logger?->info('No analytics events to archive');
            return;
        }

        $eventTable = $this->eventRepository->getTableName();
        $archived = 0;

        while ($archived < $countToArchive) {
            $conn = $this->em->getConnection();
            $conn->beginTransaction();

            try {
                $inserted = $this->archiveRepository->insertFromEventTable(
                    $eventTable,
                    $cutoffDate,
                    $message->batchSize
                );

                if ($inserted === 0) {
                    $conn->rollBack();
                    break;
                }

                $this->eventRepository->deleteEventsOlderThan($cutoffDate, $message->batchSize);

                $conn->commit();
                $archived += $inserted;
            } catch (\Exception $e) {
                $conn->rollBack();
                $this->logger?->error('Error archiving analytics: {error}', [
                    'error' => $e->getMessage(),
                ]);
                throw $e;
            }
        }

        $this->logger?->info('Archived {count} analytics events older than {cutoff}', [
            'count' => $archived,
            'cutoff' => $cutoffDate->format('Y-m-d'),
        ]);
    }
}
