<?php

declare(strict_types=1);

namespace Xutim\AnalyticsBundle\Action\Admin;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapQueryParameter;
use Symfony\UX\Chartjs\Builder\ChartBuilderInterface;
use Symfony\UX\Chartjs\Model\Chart;
use Twig\Environment;
use Xutim\AnalyticsBundle\Domain\Repository\AnalyticsDailyCountryRepositoryInterface;
use Xutim\AnalyticsBundle\Domain\Repository\AnalyticsDailyDeviceRepositoryInterface;
use Xutim\AnalyticsBundle\Domain\Repository\AnalyticsDailySessionRepositoryInterface;
use Xutim\AnalyticsBundle\Domain\Repository\AnalyticsDailySummaryRepositoryInterface;
use Xutim\AnalyticsBundle\Domain\Repository\AnalyticsDailyTrafficSourceRepositoryInterface;
use Xutim\AnalyticsBundle\Service\DateRangeResolver;

class DashboardAction
{
    public function __construct(
        private readonly AnalyticsDailySummaryRepositoryInterface $summaryRepo,
        private readonly AnalyticsDailyTrafficSourceRepositoryInterface $trafficSourceRepo,
        private readonly AnalyticsDailyCountryRepositoryInterface $countryRepo,
        private readonly AnalyticsDailyDeviceRepositoryInterface $deviceRepo,
        private readonly AnalyticsDailySessionRepositoryInterface $sessionRepo,
        private readonly DateRangeResolver $dateRangeResolver,
        private readonly Environment $twig,
        private readonly ?ChartBuilderInterface $chartBuilder = null
    ) {
    }

    public function __invoke(
        #[MapQueryParameter]
        string $preset = DateRangeResolver::DEFAULT_PRESET,
        #[MapQueryParameter]
        ?string $from = null,
        #[MapQueryParameter]
        ?string $to = null
    ): Response {
        $range = $this->dateRangeResolver->resolve($preset, $from, $to);
        $prevRange = $range->previousPeriod();

        $pageviews = $this->summaryRepo->getTotalPageviews($range);
        $visitors = $this->summaryRepo->getTotalUniqueVisitors($range);
        $viewsPerVisit = $this->sessionRepo->getAveragePagesPerSession($range);
        $bounceRate = $this->sessionRepo->getBounceRate($range);
        $avgScrollDepth = $this->summaryRepo->getAverageScrollDepth($range);
        $avgLoadTime = $this->summaryRepo->getAverageLoadTime($range);

        $prevPageviews = $this->summaryRepo->getTotalPageviews($prevRange);
        $prevVisitors = $this->summaryRepo->getTotalUniqueVisitors($prevRange);
        $prevViewsPerVisit = $this->sessionRepo->getAveragePagesPerSession($prevRange);
        $prevBounceRate = $this->sessionRepo->getBounceRate($prevRange);
        $prevAvgScrollDepth = $this->summaryRepo->getAverageScrollDepth($prevRange);
        $prevAvgLoadTime = $this->summaryRepo->getAverageLoadTime($prevRange);

        $pageviewsByDay = $this->summaryRepo->getPageviewsByDay($range);
        $topPages = $this->summaryRepo->findTopPagesByDateRange($range, 5);
        $referrers = $this->trafficSourceRepo->findTopSourcesByDateRange($range, 5);
        $countries = $this->countryRepo->findTopCountriesByDateRange($range, 5);
        $devices = $this->deviceRepo->findDeviceTypeBreakdown($range);

        $pageviewsChart = $this->buildPageviewsChart($pageviewsByDay);

        return new Response(
            $this->twig->render('@XutimAnalytics/admin/analytics/dashboard.html.twig', [
                'range' => $range,
                'preset' => $preset,
                'pageviews' => $pageviews,
                'visitors' => $visitors,
                'viewsPerVisit' => $viewsPerVisit,
                'bounceRate' => $bounceRate,
                'avgScrollDepth' => $avgScrollDepth,
                'avgLoadTime' => $avgLoadTime,
                'prevPageviews' => $prevPageviews,
                'prevVisitors' => $prevVisitors,
                'prevViewsPerVisit' => $prevViewsPerVisit,
                'prevBounceRate' => $prevBounceRate,
                'prevAvgScrollDepth' => $prevAvgScrollDepth,
                'prevAvgLoadTime' => $prevAvgLoadTime,
                'pageviewsByDay' => $pageviewsByDay,
                'pageviewsChart' => $pageviewsChart,
                'topPages' => $topPages,
                'referrers' => $referrers,
                'countries' => $countries,
                'devices' => $devices,
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
