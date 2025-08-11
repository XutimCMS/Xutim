<?php

declare(strict_types=1);

namespace Xutim\AnalyticsBundle\Message;

/**
 * @phpstan-type RawClick array<string, mixed>
 */
final readonly class CollectAnalyticsMessage
{
    /**
     * @param list<RawClick> $clickedElements
     */
    public function __construct(
        public readonly string $path,
        public readonly ?string $screenSize,
        public readonly ?int $loadTimeMs,
        public readonly ?int $scrollDepth,
        public readonly array $clickedElements,
        public readonly ?string $referer,
        public readonly ?string $userAgent,
        public readonly ?string $language,
        public readonly ?string $clientIp,
        public readonly ?string $country,
        public readonly ?string $hostname = null,
        public readonly ?string $referrerDomain = null,
    ) {
    }
}
