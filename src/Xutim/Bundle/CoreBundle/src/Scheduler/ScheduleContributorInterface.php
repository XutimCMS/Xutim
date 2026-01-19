<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Scheduler;

use Symfony\Component\Scheduler\RecurringMessage;

interface ScheduleContributorInterface
{
    /**
     * @return iterable<RecurringMessage>
     */
    public function getScheduledMessages(): iterable;
}
