<?php

declare(strict_types=1);

namespace Xutim\AnalyticsBundle\Message;

final readonly class ArchiveAnalyticsMessage
{
    public function __construct(
        public int $retentionDays = 90,
        public int $batchSize = 10000,
    ) {
    }
}
