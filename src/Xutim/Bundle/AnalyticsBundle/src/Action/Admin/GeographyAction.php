<?php

declare(strict_types=1);

namespace Xutim\AnalyticsBundle\Action\Admin;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapQueryParameter;
use Twig\Environment;
use Xutim\AnalyticsBundle\Domain\Repository\AnalyticsDailyCountryRepositoryInterface;
use Xutim\AnalyticsBundle\Service\DateRangeResolver;

class GeographyAction
{
    public function __construct(
        private readonly AnalyticsDailyCountryRepositoryInterface $countryRepo,
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

        $countries = $this->countryRepo->findTopCountriesByDateRange($range, 100);

        return new Response(
            $this->twig->render('@XutimAnalytics/admin/analytics/geography.html.twig', [
                'range' => $range,
                'preset' => $preset,
                'countries' => $countries,
            ])
        );
    }
}
