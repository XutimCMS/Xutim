<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Twig\Extension;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

final class AnalyticsExtension extends AbstractExtension
{
    /**
     * @param array<string, class-string> $bundles
     */
    public function __construct(
        private readonly array $bundles,
    ) {
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('xutim_analytics_enabled', $this->isAnalyticsEnabled(...)),
        ];
    }

    public function isAnalyticsEnabled(): bool
    {
        return array_key_exists('XutimAnalyticsBundle', $this->bundles);
    }
}
