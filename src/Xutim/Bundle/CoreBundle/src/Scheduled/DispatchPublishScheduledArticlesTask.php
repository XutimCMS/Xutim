<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Scheduled;

use Symfony\Component\Scheduler\Attribute\AsSchedule;
use Symfony\Component\Scheduler\RecurringMessage;
use Symfony\Component\Scheduler\Schedule;
use Symfony\Component\Scheduler\ScheduleProviderInterface;
use Symfony\Component\Scheduler\Trigger\PeriodicalTrigger;
use Symfony\Contracts\Cache\CacheInterface;
use Xutim\CoreBundle\Message\Command\Article\PublishScheduledArticlesCommand;
use Xutim\SecurityBundle\Console\CreateUserCliCommand;

#[AsSchedule()]
final class DispatchPublishScheduledArticlesTask implements ScheduleProviderInterface
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
            ->stateful($this->cache)
            ->processOnlyLastMissedRun(true)
        ;
    }
}
