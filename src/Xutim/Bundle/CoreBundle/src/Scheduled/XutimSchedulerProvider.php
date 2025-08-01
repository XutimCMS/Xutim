<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Scheduled;

use Cron\CronExpression;
use Symfony\Component\Scheduler\RecurringMessage;
use Symfony\Component\Scheduler\Schedule;
use Symfony\Component\Scheduler\ScheduleProviderInterface;
use Symfony\Component\Scheduler\Trigger\CronExpressionTrigger;
use Symfony\Component\Scheduler\Trigger\PeriodicalTrigger;
use Symfony\Contracts\Cache\CacheInterface;
use Xutim\CoreBundle\Message\Command\Article\PublishScheduledArticlesCommand;
use Xutim\CoreBundle\Message\Command\GenerateSitemapCommand;
use Xutim\SecurityBundle\Console\CreateUserCliCommand;

final class XutimSchedulerProvider implements ScheduleProviderInterface
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
                    new PeriodicalTrigger(60),
                    new PublishScheduledArticlesCommand(CreateUserCliCommand::COMMAND_USER)
                )
            )
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
