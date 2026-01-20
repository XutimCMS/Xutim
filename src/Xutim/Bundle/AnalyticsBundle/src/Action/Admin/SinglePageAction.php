<?php

declare(strict_types=1);

namespace Xutim\AnalyticsBundle\Action\Admin;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapQueryParameter;
use Symfony\UX\Chartjs\Builder\ChartBuilderInterface;
use Symfony\UX\Chartjs\Model\Chart;
use Twig\Environment;
use Xutim\AnalyticsBundle\Domain\Repository\AnalyticsDailyPageReferrerRepositoryInterface;
use Xutim\AnalyticsBundle\Domain\Repository\AnalyticsDailySessionRepositoryInterface;
use Xutim\AnalyticsBundle\Domain\Repository\AnalyticsDailySummaryRepositoryInterface;
use Xutim\AnalyticsBundle\Service\DateRangeResolver;

class SinglePageAction
{
    public function __construct(
        private readonly AnalyticsDailySummaryRepositoryInterface $summaryRepo,
        private readonly AnalyticsDailyPageReferrerRepositoryInterface $pageReferrerRepo,
        private readonly AnalyticsDailySessionRepositoryInterface $sessionRepo,
        private readonly DateRangeResolver $dateRangeResolver,
        private readonly Environment $twig,
        private readonly ?ChartBuilderInterface $chartBuilder = null
    ) {
    }

    public function __invoke(
        Request $request,
        #[MapQueryParameter]
        string $preset = DateRangeResolver::DEFAULT_PRESET,
        #[MapQueryParameter]
        ?string $from = null,
        #[MapQueryParameter]
        ?string $to = null
    ): Response {
        $path = $request->query->getString('path', '/');
        $range = $this->dateRangeResolver->resolve($preset, $from, $to);
        $prevRange = $range->previousPeriod();

        $pageStats = $this->summaryRepo->getPageStats($path, $range);
        $bounceRate = $this->sessionRepo->getBounceRateForEntryPage($path, $range);
        $pageviewsByDay = $this->summaryRepo->getPageviewsByDayForPath($path, $range);
        $referrers = $this->pageReferrerRepo->findReferrersForPage($path, $range, 50);

        $prevPageStats = $this->summaryRepo->getPageStats($path, $prevRange);
        $prevBounceRate = $this->sessionRepo->getBounceRateForEntryPage($path, $prevRange);

        $pageviewsChart = $this->buildPageviewsChart($pageviewsByDay);

        return new Response(
            $this->twig->render('@XutimAnalytics/admin/analytics/page_detail.html.twig', [
                'path' => $path,
                'range' => $range,
                'preset' => $preset,
                'pageStats' => $pageStats,
                'bounceRate' => $bounceRate,
                'prevPageStats' => $prevPageStats,
                'prevBounceRate' => $prevBounceRate,
                'pageviewsByDay' => $pageviewsByDay,
                'pageviewsChart' => $pageviewsChart,
                'referrers' => $referrers,
            ])
        );
    }

    /**
     * @param array<int, array{date: string, pageviews: int}> $pageviewsByDay
     */
    private function buildPageviewsChart(array $pageviewsByDay): ?Chart
    {
        if ($this->chartBuilder === null || $pageviewsByDay === []) {
            return null;
        }

        $chart = $this->chartBuilder->createChart(Chart::TYPE_LINE);
        $chart->setData([
            'labels' => array_column($pageviewsByDay, 'date'),
            'datasets' => [[
                'label' => 'Pageviews',
                'data' => array_column($pageviewsByDay, 'pageviews'),
                'borderColor' => '#f59f00',
                'backgroundColor' => 'rgba(245, 159, 0, 0.1)',
                'tension' => 0.3,
                'fill' => true,
            ]],
        ]);
        $chart->setOptions([
            'maintainAspectRatio' => false,
            'plugins' => [
                'legend' => ['display' => false],
            ],
            'scales' => [
                'y' => ['beginAtZero' => true],
            ],
        ]);

        return $chart;
    }
}
