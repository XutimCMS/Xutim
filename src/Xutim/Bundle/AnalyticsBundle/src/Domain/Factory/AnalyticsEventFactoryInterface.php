<?php

declare(strict_types=1);

namespace Xutim\AnalyticsBundle\Domain\Factory;

use Xutim\AnalyticsBundle\Domain\Model\AnalyticsEventInterface;

interface AnalyticsEventFactoryInterface
{
    /**
     * @param list<array{
     *     tag: string,
     *     id?: string,
     *     class?: string,
     *     role?: string,
     *     text?: string
     * }> $clickedElements
     */
    public function create(
        string $path,
        ?string $referer,
        ?string $userAgent,
        ?string $language,
        ?string $screenSize,
        ?int $loadTimeMs,
        ?int $scrollDepth,
        array $clickedElements,
        ?string $country,
        bool $isBot,
        ?string $anonymizedIp,
        string $sessionBucket,
    ): AnalyticsEventInterface;
}
