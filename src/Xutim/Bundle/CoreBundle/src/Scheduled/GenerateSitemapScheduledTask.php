<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Scheduled;

use Cron\CronExpression;
use Symfony\Component\Scheduler\Attribute\AsSchedule;
use Symfony\Component\Scheduler\RecurringMessage;
use Symfony\Component\Scheduler\Schedule;
use Symfony\Component\Scheduler\ScheduleProviderInterface;
use Symfony\Component\Scheduler\Trigger\CronExpressionTrigger;
use Symfony\Contracts\Cache\CacheInterface;
use Xutim\CoreBundle\Message\Command\GenerateSitemapCommand;

#[AsSchedule()]
final class GenerateSitemapScheduledTask implements ScheduleProviderInterface
{
    public function __construct(
        private readonly CacheInterface $cache
    ) {
    }

    public function getSchedule(): Schedule
    {
        return new Schedule()
            ->add(
                RecurringMessage::trigger(
                    new CronExpressionTrigger(new CronExpression('0 2 * * *')), // daily at 2 AM
                    new GenerateSitemapCommand()
                )
            )
            ->stateful($this->cache)
            ->processOnlyLastMissedRun(true)
        ;
    }
}
