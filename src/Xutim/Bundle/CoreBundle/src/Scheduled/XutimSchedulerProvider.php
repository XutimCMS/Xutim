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
use Xutim\CoreBundle\Scheduler\ScheduleContributorInterface;
use Xutim\SecurityBundle\Console\CreateUserCliCommand;

final class XutimSchedulerProvider implements ScheduleProviderInterface
{
    /**
     * @param iterable<ScheduleContributorInterface> $contributors
     */
    public function __construct(
        private readonly CacheInterface $cache,
        private readonly iterable $contributors = [],
    ) {
    }

    public function getSchedule(): Schedule
    {
        $schedule = new Schedule();

        $schedule
            ->add(
                RecurringMessage::trigger(
                    new PeriodicalTrigger(60),
                    new PublishScheduledArticlesCommand(CreateUserCliCommand::COMMAND_USER)
                )
            )
            ->add(
                RecurringMessage::trigger(
                    new CronExpressionTrigger(new CronExpression('0 2 * * *')),
                    new GenerateSitemapCommand()
                )
            )
        ;

        foreach ($this->contributors as $contributor) {
            foreach ($contributor->getScheduledMessages() as $message) {
                $schedule->add($message);
            }
        }

        return $schedule
            ->stateful($this->cache)
            ->processOnlyLastMissedRun(true)
        ;
    }
}
