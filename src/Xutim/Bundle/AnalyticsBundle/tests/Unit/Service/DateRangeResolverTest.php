<?php

declare(strict_types=1);

namespace Xutim\AnalyticsBundle\Tests\Unit\Service;

use PHPUnit\Framework\TestCase;
use Xutim\AnalyticsBundle\Service\DateRangeResolver;

final class DateRangeResolverTest extends TestCase
{
    private DateRangeResolver $resolver;

    protected function setUp(): void
    {
        $this->resolver = new DateRangeResolver();
    }

    public function testResolveTodayReturnsToday(): void
    {
        $range = $this->resolver->resolve(DateRangeResolver::PRESET_TODAY);

        $today = new \DateTimeImmutable();
        $this->assertSame($today->format('Y-m-d'), $range->from->format('Y-m-d'));
        $this->assertSame($today->format('Y-m-d'), $range->to->format('Y-m-d'));
        $this->assertSame('00:00:00', $range->from->format('H:i:s'));
        $this->assertSame('23:59:59', $range->to->format('H:i:s'));
    }

    public function testResolve7dReturnsLast7Days(): void
    {
        $range = $this->resolver->resolve(DateRangeResolver::PRESET_7D);

        $today = new \DateTimeImmutable();
        $sixDaysAgo = $today->modify('-6 days');

        $this->assertSame($sixDaysAgo->format('Y-m-d'), $range->from->format('Y-m-d'));
        $this->assertSame($today->format('Y-m-d'), $range->to->format('Y-m-d'));
        $this->assertSame(7, $range->getDays());
    }

    public function testResolve30dReturnsLast30Days(): void
    {
        $range = $this->resolver->resolve(DateRangeResolver::PRESET_30D);

        $today = new \DateTimeImmutable();
        $twentyNineDaysAgo = $today->modify('-29 days');

        $this->assertSame($twentyNineDaysAgo->format('Y-m-d'), $range->from->format('Y-m-d'));
        $this->assertSame($today->format('Y-m-d'), $range->to->format('Y-m-d'));
        $this->assertSame(30, $range->getDays());
    }

    public function testResolveCustomWithValidDates(): void
    {
        $range = $this->resolver->resolve(DateRangeResolver::PRESET_CUSTOM, '2024-01-01', '2024-01-31');

        $this->assertSame('2024-01-01', $range->from->format('Y-m-d'));
        $this->assertSame('2024-01-31', $range->to->format('Y-m-d'));
        $this->assertSame('00:00:00', $range->from->format('H:i:s'));
        $this->assertSame('23:59:59', $range->to->format('H:i:s'));
    }

    public function testResolveCustomWithInvalidFromFallsBackToDefault(): void
    {
        $range = $this->resolver->resolve(DateRangeResolver::PRESET_CUSTOM, 'invalid-date', '2024-01-31');

        $today = new \DateTimeImmutable();
        $sixDaysAgo = $today->modify('-6 days');

        $this->assertSame($sixDaysAgo->format('Y-m-d'), $range->from->format('Y-m-d'));
        $this->assertSame($today->format('Y-m-d'), $range->to->format('Y-m-d'));
    }

    public function testResolveCustomWithNullFromUsesDefault(): void
    {
        $range = $this->resolver->resolve(DateRangeResolver::PRESET_CUSTOM, null, '2024-01-31');

        $sevenDaysAgo = new \DateTimeImmutable('-7 days');
        $this->assertSame($sevenDaysAgo->format('Y-m-d'), $range->from->format('Y-m-d'));
        $this->assertSame('2024-01-31', $range->to->format('Y-m-d'));
    }

    public function testResolveCustomWithNullToUsesToday(): void
    {
        $range = $this->resolver->resolve(DateRangeResolver::PRESET_CUSTOM, '2024-01-01', null);

        $today = new \DateTimeImmutable();
        $this->assertSame('2024-01-01', $range->from->format('Y-m-d'));
        $this->assertSame($today->format('Y-m-d'), $range->to->format('Y-m-d'));
    }

    public function testResolveInvalidPresetDefaultsTo7d(): void
    {
        $range = $this->resolver->resolve('invalid');

        $today = new \DateTimeImmutable();
        $sixDaysAgo = $today->modify('-6 days');

        $this->assertSame($sixDaysAgo->format('Y-m-d'), $range->from->format('Y-m-d'));
        $this->assertSame($today->format('Y-m-d'), $range->to->format('Y-m-d'));
    }

    public function testResolveEmptyPresetDefaultsTo7d(): void
    {
        $range = $this->resolver->resolve('');

        $today = new \DateTimeImmutable();
        $sixDaysAgo = $today->modify('-6 days');

        $this->assertSame($sixDaysAgo->format('Y-m-d'), $range->from->format('Y-m-d'));
        $this->assertSame($today->format('Y-m-d'), $range->to->format('Y-m-d'));
    }

    public function testDefaultPresetConstantIs7d(): void
    {
        $this->assertSame(DateRangeResolver::PRESET_7D, DateRangeResolver::DEFAULT_PRESET);
    }
}
