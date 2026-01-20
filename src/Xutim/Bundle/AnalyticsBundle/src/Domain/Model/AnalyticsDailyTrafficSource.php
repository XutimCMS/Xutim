<?php

declare(strict_types=1);

namespace Xutim\AnalyticsBundle\Domain\Model;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\MappedSuperclass;

#[MappedSuperclass]
class AnalyticsDailyTrafficSource implements AnalyticsDailyTrafficSourceInterface
{
    #[Id]
    #[Column(type: 'date_immutable')]
    private readonly \DateTimeImmutable $date;

    #[Id]
    #[Column(type: 'string', length: 255)]
    private readonly string $source;

    #[Column(type: 'integer')]
    private readonly int $visits;

    #[Column(type: 'integer')]
    private readonly int $uniqueVisitors;

    public function __construct(
        \DateTimeImmutable $date,
        string $source,
        int $visits,
        int $uniqueVisitors
    ) {
        $this->date = $date;
        $this->source = $source;
        $this->visits = $visits;
        $this->uniqueVisitors = $uniqueVisitors;
    }

    public function getDate(): \DateTimeImmutable
    {
        return $this->date;
    }

    public function getSource(): string
    {
        return $this->source;
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
