<?php

declare(strict_types=1);

namespace Xutim\AnalyticsBundle\Action\Admin;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapQueryParameter;
use Twig\Environment;
use Xutim\AnalyticsBundle\Domain\Repository\AnalyticsDailySummaryRepositoryInterface;
use Xutim\AnalyticsBundle\Service\DateRangeResolver;

class PagesListAction
{
    public function __construct(
        private readonly AnalyticsDailySummaryRepositoryInterface $summaryRepo,
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

        $topPages = $this->summaryRepo->findTopPagesByDateRange($range, 50);

        return new Response(
            $this->twig->render('@XutimAnalytics/admin/analytics/pages.html.twig', [
                'range' => $range,
                'preset' => $preset,
                'pages' => $topPages,
            ])
        );
    }
}
