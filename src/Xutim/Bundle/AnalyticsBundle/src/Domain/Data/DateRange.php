<?php

declare(strict_types=1);

namespace Xutim\AnalyticsBundle\Domain\Data;

final readonly class DateRange
{
    public function __construct(
        public \DateTimeImmutable $from,
        public \DateTimeImmutable $to
    ) {
    }

    public static function today(): self
    {
        $now = new \DateTimeImmutable();

        return new self(
            $now->setTime(0, 0, 0),
            $now->setTime(23, 59, 59)
        );
    }

    public static function last7Days(): self
    {
        $now = new \DateTimeImmutable();

        return new self(
            $now->modify('-6 days')->setTime(0, 0, 0),
            $now->setTime(23, 59, 59)
        );
    }

    public static function last30Days(): self
    {
        $now = new \DateTimeImmutable();

        return new self(
            $now->modify('-29 days')->setTime(0, 0, 0),
            $now->setTime(23, 59, 59)
        );
    }

    public static function custom(\DateTimeImmutable $from, \DateTimeImmutable $to): self
    {
        return new self(
            $from->setTime(0, 0, 0),
            $to->setTime(23, 59, 59)
        );
    }

    public function getDays(): int
    {
        return (int) $this->from->diff($this->to)->days + 1;
    }

    public function previousPeriod(): self
    {
        $days = $this->getDays();

        return new self(
            $this->from->modify("-{$days} days"),
            $this->from->modify('-1 day')->setTime(23, 59, 59)
        );
    }
}
