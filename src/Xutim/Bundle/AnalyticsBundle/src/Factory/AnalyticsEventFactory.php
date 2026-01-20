<?php

declare(strict_types=1);

namespace Xutim\AnalyticsBundle\Factory;

use Xutim\AnalyticsBundle\Domain\Factory\AnalyticsEventFactoryInterface;
use Xutim\AnalyticsBundle\Domain\Model\AnalyticsEventInterface;

class AnalyticsEventFactory implements AnalyticsEventFactoryInterface
{
    public function __construct(private readonly string $entityClass)
    {
        if (!class_exists($entityClass)) {
            throw new \InvalidArgumentException(sprintf('Analytics event class "%s" does not exist.', $entityClass));
        }
    }

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
        ?string $utmSource = null,
        ?string $utmMedium = null,
        ?string $utmCampaign = null
    ): AnalyticsEventInterface {
        /** @var AnalyticsEventInterface $event */
        $event = new ($this->entityClass)(
            $path,
            $referer,
            $userAgent,
            $language,
            $screenSize,
            $loadTimeMs,
            $scrollDepth,
            $clickedElements,
            $country,
            $isBot,
            $anonymizedIp,
            $sessionBucket,
            $utmSource,
            $utmMedium,
            $utmCampaign
        );

        return $event;
    }
}
