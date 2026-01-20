<?php

declare(strict_types=1);

namespace Xutim\AnalyticsBundle\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Xutim\AnalyticsBundle\Service\AnalyticsAggregationService;

#[AsCommand(
    name: 'xutim:analytics:aggregate',
    description: 'Aggregate raw analytics events into daily summary tables'
)]
final class AggregateAnalyticsCommand extends Command
{
    public function __construct(
        private readonly AnalyticsAggregationService $aggregationService,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addArgument(
            'date',
            InputArgument::OPTIONAL,
            'Date to aggregate (YYYY-MM-DD format, defaults to yesterday)'
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $dateArg = $input->getArgument('date');
        if ($dateArg !== null && !is_string($dateArg)) {
            $io->error('Invalid date argument');
            return Command::FAILURE;
        }

        $date = $dateArg !== null
            ? new \DateTimeImmutable($dateArg)
            : new \DateTimeImmutable('yesterday');

        $dateStr = $date->format('Y-m-d');
        $io->title(sprintf('Aggregating analytics for %s', $dateStr));

        $eventCount = $this->aggregationService->countEventsForDate($date);
        $io->text(sprintf('Found %d raw events to aggregate', $eventCount));

        if ($eventCount === 0) {
            $io->warning('No events to aggregate');
            return Command::SUCCESS;
        }

        $io->text('Aggregating daily summary...');
        $summaryCount = $this->aggregationService->aggregateDailySummary($date);
        $io->text(sprintf('  → %d paths aggregated', $summaryCount));

        $io->text('Aggregating daily traffic sources...');
        $sourceCount = $this->aggregationService->aggregateDailyTrafficSource($date);
        $io->text(sprintf('  → %d sources aggregated', $sourceCount));

        $io->text('Aggregating daily countries...');
        $countryCount = $this->aggregationService->aggregateDailyCountry($date);
        $io->text(sprintf('  → %d countries aggregated', $countryCount));

        $io->text('Aggregating daily devices (parsing user agents)...');
        $deviceCount = $this->aggregationService->aggregateDailyDevice($date);
        $io->text(sprintf('  → %d device combinations aggregated', $deviceCount));

        $io->text('Aggregating daily UTM campaigns...');
        $utmCount = $this->aggregationService->aggregateDailyUtm($date);
        $io->text(sprintf('  → %d UTM combinations aggregated', $utmCount));

        $io->text('Aggregating daily page referrers...');
        $referrerCount = $this->aggregationService->aggregateDailyPageReferrer($date);
        $io->text(sprintf('  → %d page referrer combinations aggregated', $referrerCount));

        $io->text('Aggregating daily sessions...');
        $sessionCount = $this->aggregationService->aggregateDailySession($date);
        $io->text(sprintf('  → %d session combinations aggregated', $sessionCount));

        $io->success(sprintf('Aggregation complete for %s', $dateStr));

        return Command::SUCCESS;
    }
}
