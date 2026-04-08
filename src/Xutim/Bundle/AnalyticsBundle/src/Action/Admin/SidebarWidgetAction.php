<?php

declare(strict_types=1);

namespace Xutim\AnalyticsBundle\Action\Admin;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\Cache;
use Symfony\Component\HttpKernel\Attribute\MapQueryParameter;
use Twig\Environment;
use Xutim\AnalyticsBundle\Domain\Data\DateRange;
use Xutim\AnalyticsBundle\Domain\Repository\AnalyticsDailySessionRepositoryInterface;
use Xutim\AnalyticsBundle\Domain\Repository\AnalyticsDailySummaryRepositoryInterface;

#[Cache(maxage: 300, smaxage: 300)]
class SidebarWidgetAction
{
    public function __construct(
        private readonly AnalyticsDailySummaryRepositoryInterface $summaryRepo,
        private readonly AnalyticsDailySessionRepositoryInterface $sessionRepo,
        private readonly Environment $twig,
    ) {
    }

    public function __invoke(
        #[MapQueryParameter]
        string $path = '/',
    ): Response {
        $range = DateRange::last30Days();

        $pageStats = $this->summaryRepo->getPageStats($path, $range);
        $bounceRate = $this->sessionRepo->getBounceRateForEntryPage($path, $range);
        $rawSparkline = $this->summaryRepo->getPageviewsByDayForPath($path, $range);

        $byDate = [];
        foreach ($rawSparkline as $data) {
            $byDate[$data['date'] instanceof \DateTimeInterface ? $data['date']->format('Y-m-d') : (string) $data['date']] = $data['pageviews'];
        }

        $sparklineData = [];
        $sparklineMax = 1;
        $cursor = new \DateTimeImmutable($range->from->format('Y-m-d'));
        $end = new \DateTimeImmutable($range->to->format('Y-m-d'));
        while ($cursor <= $end) {
            $key = $cursor->format('Y-m-d');
            $views = $byDate[$key] ?? 0;
            $sparklineData[] = ['date' => $key, 'pageviews' => $views];
            if ($views > $sparklineMax) {
                $sparklineMax = $views;
            }
            $cursor = $cursor->modify('+1 day');
        }

        return new Response(
            $this->twig->render('@XutimAnalytics/admin/analytics/_sidebar_widget.html.twig', [
                'path' => $path,
                'pageStats' => $pageStats,
                'bounceRate' => $bounceRate,
                'sparklineData' => $sparklineData,
                'sparklineMax' => $sparklineMax,
            ])
        );
    }
}
