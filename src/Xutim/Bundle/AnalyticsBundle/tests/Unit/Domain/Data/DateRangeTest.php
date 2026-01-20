<?php

declare(strict_types=1);

namespace Xutim\AnalyticsBundle\Tests\Unit\Domain\Data;

use PHPUnit\Framework\TestCase;
use Xutim\AnalyticsBundle\Domain\Data\DateRange;

final class DateRangeTest extends TestCase
{
    public function testTodayReturnsSingleDay(): void
    {
        $range = DateRange::today();

        $today = new \DateTimeImmutable();

        $this->assertSame($today->format('Y-m-d'), $range->from->format('Y-m-d'));
        $this->assertSame($today->format('Y-m-d'), $range->to->format('Y-m-d'));
        $this->assertSame('00:00:00', $range->from->format('H:i:s'));
        $this->assertSame('23:59:59', $range->to->format('H:i:s'));
        $this->assertSame(1, $range->getDays());
    }

    public function testLast7DaysReturns7Days(): void
    {
        $range = DateRange::last7Days();

        $today = new \DateTimeImmutable();
        $sixDaysAgo = $today->modify('-6 days');

        $this->assertSame($sixDaysAgo->format('Y-m-d'), $range->from->format('Y-m-d'));
        $this->assertSame($today->format('Y-m-d'), $range->to->format('Y-m-d'));
        $this->assertSame(7, $range->getDays());
    }

    public function testLast30DaysReturns30Days(): void
    {
        $range = DateRange::last30Days();

        $today = new \DateTimeImmutable();
        $twentyNineDaysAgo = $today->modify('-29 days');

        $this->assertSame($twentyNineDaysAgo->format('Y-m-d'), $range->from->format('Y-m-d'));
        $this->assertSame($today->format('Y-m-d'), $range->to->format('Y-m-d'));
        $this->assertSame(30, $range->getDays());
    }

    public function testCustomSetsBoundaries(): void
    {
        $from = new \DateTimeImmutable('2024-01-15 12:30:00');
        $to = new \DateTimeImmutable('2024-01-20 18:45:00');

        $range = DateRange::custom($from, $to);

        $this->assertSame('2024-01-15', $range->from->format('Y-m-d'));
        $this->assertSame('2024-01-20', $range->to->format('Y-m-d'));
        $this->assertSame('00:00:00', $range->from->format('H:i:s'));
        $this->assertSame('23:59:59', $range->to->format('H:i:s'));
        $this->assertSame(6, $range->getDays());
    }

    public function testCustomSameDay(): void
    {
        $date = new \DateTimeImmutable('2024-06-15');

        $range = DateRange::custom($date, $date);

        $this->assertSame('2024-06-15', $range->from->format('Y-m-d'));
        $this->assertSame('2024-06-15', $range->to->format('Y-m-d'));
        $this->assertSame(1, $range->getDays());
    }

    public function testGetDaysCalculatesCorrectly(): void
    {
        $from = new \DateTimeImmutable('2024-01-01');
        $to = new \DateTimeImmutable('2024-01-10');

        $range = DateRange::custom($from, $to);

        $this->assertSame(10, $range->getDays());
    }

    public function testImmutability(): void
    {
        $range = DateRange::today();

        $this->assertInstanceOf(\DateTimeImmutable::class, $range->from);
        $this->assertInstanceOf(\DateTimeImmutable::class, $range->to);
    }

    public function testPreviousPeriodFor7Days(): void
    {
        $range = DateRange::last7Days();
        $previous = $range->previousPeriod();

        $this->assertSame(7, $previous->getDays());
        $this->assertSame(
            $range->from->modify('-1 day')->format('Y-m-d'),
            $previous->to->format('Y-m-d')
        );
        $this->assertSame(
            $range->from->modify('-7 days')->format('Y-m-d'),
            $previous->from->format('Y-m-d')
        );
    }

    public function testPreviousPeriodForCustomRange(): void
    {
        $from = new \DateTimeImmutable('2024-01-10');
        $to = new \DateTimeImmutable('2024-01-15');
        $range = DateRange::custom($from, $to);

        $previous = $range->previousPeriod();

        $this->assertSame(6, $previous->getDays());
        $this->assertSame('2024-01-04', $previous->from->format('Y-m-d'));
        $this->assertSame('2024-01-09', $previous->to->format('Y-m-d'));
    }

    public function testPreviousPeriodForToday(): void
    {
        $range = DateRange::today();
        $previous = $range->previousPeriod();

        $yesterday = new \DateTimeImmutable('yesterday');

        $this->assertSame(1, $previous->getDays());
        $this->assertSame($yesterday->format('Y-m-d'), $previous->from->format('Y-m-d'));
        $this->assertSame($yesterday->format('Y-m-d'), $previous->to->format('Y-m-d'));
    }
}
