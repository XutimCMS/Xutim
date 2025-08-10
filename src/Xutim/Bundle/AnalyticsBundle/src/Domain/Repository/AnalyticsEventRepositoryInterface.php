<?php

declare(strict_types=1);

namespace Xutim\AnalyticsBundle\Domain\Repository;

use Xutim\AnalyticsBundle\Domain\Model\AnalyticsEventInterface;

interface AnalyticsEventRepositoryInterface
{
    public function save(AnalyticsEventInterface $entity, bool $andFlush = false): void;

    public function remove(AnalyticsEventInterface $entity, bool $andFlush = false): void;
}
