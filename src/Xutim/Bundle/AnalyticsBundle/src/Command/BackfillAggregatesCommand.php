<?php

declare(strict_types=1);

namespace Xutim\AnalyticsBundle\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Xutim\AnalyticsBundle\Domain\Repository\AnalyticsEventRepositoryInterface;
use Xutim\AnalyticsBundle\Service\AnalyticsAggregationService;

#[AsCommand(
    name: 'xutim:analytics:backfill',
    description: 'Backfill daily aggregation tables from historical raw events'
)]
final class BackfillAggregatesCommand extends Command
{
    public function __construct(
        private readonly AnalyticsAggregationService $aggregationService,
        private readonly AnalyticsEventRepositoryInterface $eventRepository,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addOption(
                'from',
                null,
                InputOption::VALUE_REQUIRED,
                'Start date (YYYY-MM-DD format, defaults to earliest event date)'
            )
            ->addOption(
                'to',
                null,
                InputOption::VALUE_REQUIRED,
                'End date (YYYY-MM-DD format, defaults to yesterday)'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->title('Backfilling Analytics Aggregates');

        $fromOption = $input->getOption('from');
        $toOption = $input->getOption('to');

        if ($fromOption !== null && !is_string($fromOption)) {
            $io->error('Invalid --from option');
            return Command::FAILURE;
        }

        if ($toOption !== null && !is_string($toOption)) {
            $io->error('Invalid --to option');
            return Command::FAILURE;
        }

        $fromDate = $fromOption !== null
            ? new \DateTimeImmutable($fromOption)
            : $this->eventRepository->getEarliestEventDate();

        if ($fromDate === null) {
            $io->warning('No events found in database');
            return Command::SUCCESS;
        }

        $toDate = $toOption !== null
            ? new \DateTimeImmutable($toOption)
            : new \DateTimeImmutable('yesterday');

        $fromDate = $fromDate->setTime(0, 0, 0);
        $toDate = $toDate->setTime(0, 0, 0);

        if ($fromDate > $toDate) {
            $io->error('From date must be before or equal to to date');
            return Command::FAILURE;
        }

        $io->text(sprintf(
            'Processing events from %s to %s',
            $fromDate->format('Y-m-d'),
            $toDate->format('Y-m-d')
        ));

        $currentDate = $fromDate;
        $totalDays = 0;
        $totalRows = 0;

        $io->progressStart((int) $fromDate->diff($toDate)->days + 1);

        while ($currentDate <= $toDate) {
            $eventCount = $this->aggregationService->countEventsForDate($currentDate);

            if ($eventCount > 0) {
                $results = $this->aggregationService->aggregateAll($currentDate);
                $dayRows = array_sum($results);
                $totalRows += $dayRows;
            }

            $totalDays++;
            $io->progressAdvance();
            $currentDate = $currentDate->modify('+1 day');
        }

        $io->progressFinish();

        $io->success(sprintf(
            'Backfill complete: processed %d days, created %d aggregate rows',
            $totalDays,
            $totalRows
        ));

        return Command::SUCCESS;
    }
}
