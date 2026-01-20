<?php

declare(strict_types=1);

namespace Xutim\AnalyticsBundle\Domain\Model;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\MappedSuperclass;

#[MappedSuperclass]
class AnalyticsDailyUtm implements AnalyticsDailyUtmInterface
{
    #[Id]
    #[Column(type: 'date_immutable')]
    private readonly \DateTimeImmutable $date;

    #[Id]
    #[Column(type: 'string', length: 255)]
    private readonly string $utmSource;

    #[Id]
    #[Column(type: 'string', length: 255)]
    private readonly string $utmMedium;

    #[Id]
    #[Column(type: 'string', length: 255)]
    private readonly string $utmCampaign;

    #[Column(type: 'integer')]
    private readonly int $visits;

    #[Column(type: 'integer')]
    private readonly int $uniqueVisitors;

    public function __construct(
        \DateTimeImmutable $date,
        string $utmSource,
        string $utmMedium,
        string $utmCampaign,
        int $visits,
        int $uniqueVisitors
    ) {
        $this->date = $date;
        $this->utmSource = $utmSource;
        $this->utmMedium = $utmMedium;
        $this->utmCampaign = $utmCampaign;
        $this->visits = $visits;
        $this->uniqueVisitors = $uniqueVisitors;
    }

    public function getDate(): \DateTimeImmutable
    {
        return $this->date;
    }

    public function getUtmSource(): string
    {
        return $this->utmSource;
    }

    public function getUtmMedium(): string
    {
        return $this->utmMedium;
    }

    public function getUtmCampaign(): string
    {
        return $this->utmCampaign;
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
