<?php

declare(strict_types=1);

namespace Xutim\AnalyticsBundle\Domain\Model;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\MappedSuperclass;

#[MappedSuperclass]
class AnalyticsDailyCountry implements AnalyticsDailyCountryInterface
{
    #[Id]
    #[Column(type: 'date_immutable')]
    private readonly \DateTimeImmutable $date;

    #[Id]
    #[Column(type: 'string', length: 2)]
    private readonly string $country;

    #[Column(type: 'integer')]
    private readonly int $visits;

    #[Column(type: 'integer')]
    private readonly int $uniqueVisitors;

    public function __construct(
        \DateTimeImmutable $date,
        string $country,
        int $visits,
        int $uniqueVisitors
    ) {
        $this->date = $date;
        $this->country = $country;
        $this->visits = $visits;
        $this->uniqueVisitors = $uniqueVisitors;
    }

    public function getDate(): \DateTimeImmutable
    {
        return $this->date;
    }

    public function getCountry(): string
    {
        return $this->country;
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
