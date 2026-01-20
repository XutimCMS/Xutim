<?php

declare(strict_types=1);

namespace Xutim\AnalyticsBundle\Action\Admin;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapQueryParameter;
use Twig\Environment;
use Xutim\AnalyticsBundle\Domain\Repository\AnalyticsDailyPageReferrerRepositoryInterface;
use Xutim\AnalyticsBundle\Domain\Repository\AnalyticsDailyTrafficSourceRepositoryInterface;
use Xutim\AnalyticsBundle\Service\DateRangeResolver;

class TrafficSourcesAction
{
    public function __construct(
        private readonly AnalyticsDailyTrafficSourceRepositoryInterface $trafficSourceRepo,
        private readonly AnalyticsDailyPageReferrerRepositoryInterface $pageReferrerRepo,
        private readonly DateRangeResolver $dateRangeResolver,
        private readonly Environment $twig
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

        $sources = $this->trafficSourceRepo->findTopSourcesByDateRange($range, 100);
        $rawReferrers = $this->pageReferrerRepo->findTopExternalReferrers($range, 100);

        return new Response(
            $this->twig->render('@XutimAnalytics/admin/analytics/sources.html.twig', [
                'range' => $range,
                'preset' => $preset,
                'sources' => $sources,
                'rawReferrers' => $rawReferrers,
            ])
        );
    }
}
