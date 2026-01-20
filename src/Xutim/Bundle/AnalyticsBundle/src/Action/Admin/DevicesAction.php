<?php

declare(strict_types=1);

namespace Xutim\AnalyticsBundle\Action\Admin;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapQueryParameter;
use Twig\Environment;
use Xutim\AnalyticsBundle\Domain\Repository\AnalyticsDailyDeviceRepositoryInterface;
use Xutim\AnalyticsBundle\Service\DateRangeResolver;

class DevicesAction
{
    public function __construct(
        private readonly AnalyticsDailyDeviceRepositoryInterface $deviceRepo,
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

        $devices = $this->deviceRepo->findDeviceTypeBreakdown($range);
        $browsers = $this->deviceRepo->findBrowserBreakdown($range);
        $operatingSystems = $this->deviceRepo->findOsBreakdown($range);

        return new Response(
            $this->twig->render('@XutimAnalytics/admin/analytics/devices.html.twig', [
                'range' => $range,
                'preset' => $preset,
                'devices' => $devices,
                'browsers' => $browsers,
                'operatingSystems' => $operatingSystems,
            ])
        );
    }
}
