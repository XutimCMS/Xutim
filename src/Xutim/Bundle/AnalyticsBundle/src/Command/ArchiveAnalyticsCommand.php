<?php

declare(strict_types=1);

namespace Xutim\AnalyticsBundle\Command;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Xutim\AnalyticsBundle\Domain\Repository\AnalyticsEventArchiveRepositoryInterface;
use Xutim\AnalyticsBundle\Domain\Repository\AnalyticsEventRepositoryInterface;

#[AsCommand(
    name: 'xutim:analytics:archive',
    description: 'Archive raw analytics events older than retention period'
)]
final class ArchiveAnalyticsCommand extends Command
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly AnalyticsEventRepositoryInterface $eventRepository,
        private readonly AnalyticsEventArchiveRepositoryInterface $archiveRepository,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addOption(
                'retention-days',
                null,
                InputOption::VALUE_REQUIRED,
                'Number of days to keep raw events (default: 90)',
                '90'
            )
            ->addOption(
                'batch-size',
                null,
                InputOption::VALUE_REQUIRED,
                'Number of rows to process per batch (default: 10000)',
                '10000'
            )
            ->addOption(
                'dry-run',
                null,
                InputOption::VALUE_NONE,
                'Show what would be archived without actually doing it'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        /** @var string $retentionDaysOption */
        $retentionDaysOption = $input->getOption('retention-days');
        $retentionDays = (int) $retentionDaysOption;

        /** @var string $batchSizeOption */
        $batchSizeOption = $input->getOption('batch-size');
        $batchSize = (int) $batchSizeOption;

        $dryRun = (bool) $input->getOption('dry-run');

        if ($retentionDays < 1) {
            $io->error('Retention days must be at least 1');
            return Command::FAILURE;
        }

        $cutoffDate = (new \DateTimeImmutable())
            ->modify(sprintf('-%d days', $retentionDays))
            ->setTime(0, 0, 0);

        $io->title('Archiving Analytics Events');
        $io->text(sprintf('Retention period: %d days', $retentionDays));
        $io->text(sprintf('Archiving events older than: %s', $cutoffDate->format('Y-m-d')));

        if ($dryRun) {
            $io->note('DRY RUN - no changes will be made');
        }

        $countToArchive = $this->eventRepository->countEventsOlderThan($cutoffDate);
        $io->text(sprintf('Events to archive: %d', $countToArchive));

        if ($countToArchive === 0) {
            $io->success('No events to archive');
            return Command::SUCCESS;
        }

        if ($dryRun) {
            $io->success(sprintf('Would archive %d events (dry run)', $countToArchive));
            return Command::SUCCESS;
        }

        $eventTable = $this->eventRepository->getTableName();
        $archived = 0;
        $io->progressStart($countToArchive);

        while ($archived < $countToArchive) {
            $conn = $this->em->getConnection();
            $conn->beginTransaction();

            try {
                $inserted = $this->archiveRepository->insertFromEventTable($eventTable, $cutoffDate, $batchSize);

                if ($inserted === 0) {
                    $conn->rollBack();
                    break;
                }

                $this->eventRepository->deleteEventsOlderThan($cutoffDate, $batchSize);

                $conn->commit();

                $archived += $inserted;
                $io->progressAdvance($inserted);
            } catch (\Exception $e) {
                $conn->rollBack();
                $io->error(sprintf('Error during archiving: %s', $e->getMessage()));
                return Command::FAILURE;
            }
        }

        $io->progressFinish();

        $archiveCount = $this->archiveRepository->countAll();
        $io->success(sprintf(
            'Archived %d events. Total archive size: %d events',
            $archived,
            $archiveCount
        ));

        return Command::SUCCESS;
    }
}
