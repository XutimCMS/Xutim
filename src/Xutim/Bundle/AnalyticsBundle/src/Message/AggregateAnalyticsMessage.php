<?php

declare(strict_types=1);

namespace Xutim\AnalyticsBundle\Message;

final readonly class AggregateAnalyticsMessage
{
    public \DateTimeImmutable $date;

    public function __construct(
        ?\DateTimeImmutable $date = null,
    ) {
        $this->date = $date ?? new \DateTimeImmutable('yesterday');
    }
}
