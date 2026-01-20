<?php

declare(strict_types=1);

namespace Xutim\AnalyticsBundle\Domain\Model;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\MappedSuperclass;

#[MappedSuperclass]
class AnalyticsDailyDevice implements AnalyticsDailyDeviceInterface
{
    #[Id]
    #[Column(type: 'date_immutable')]
    private readonly \DateTimeImmutable $date;

    #[Id]
    #[Column(type: 'string', length: 32)]
    private readonly string $deviceType;

    #[Id]
    #[Column(type: 'string', length: 64)]
    private readonly string $browser;

    #[Id]
    #[Column(type: 'string', length: 64)]
    private readonly string $os;

    #[Column(type: 'integer')]
    private readonly int $visits;

    #[Column(type: 'integer')]
    private readonly int $uniqueVisitors;

    public function __construct(
        \DateTimeImmutable $date,
        string $deviceType,
        string $browser,
        string $os,
        int $visits,
        int $uniqueVisitors
    ) {
        $this->date = $date;
        $this->deviceType = $deviceType;
        $this->browser = $browser;
        $this->os = $os;
        $this->visits = $visits;
        $this->uniqueVisitors = $uniqueVisitors;
    }

    public function getDate(): \DateTimeImmutable
    {
        return $this->date;
    }

    public function getDeviceType(): string
    {
        return $this->deviceType;
    }

    public function getBrowser(): string
    {
        return $this->browser;
    }

    public function getOs(): string
    {
        return $this->os;
    }

    public function getVisits(): int
    {
        return $this->visits;
    }

    public function getUniqueVisitors(): int
    {
        return $this->uniqueVisitors;
    }
}
