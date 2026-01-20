# Scheduler

## With XutimCMS

If using XutimCMS, scheduling is automatic. The CoreBundle schedules:

| Schedule | Task | Description |
|----------|------|-------------|
| Every hour | Aggregate today | Real-time dashboard data |
| Daily at 2am | Aggregate yesterday | Final daily totals |
| Monthly at 3am (1st) | Archive old events | Move events to archive table |

No configuration needed.

## Standalone Setup

Without XutimCMS, set up cron jobs manually.

### Option 1: Symfony Scheduler (Recommended)

Create a schedule provider:

```php
// src/Scheduler/AnalyticsScheduleProvider.php
namespace App\Scheduler;

use Cron\CronExpression;
use Symfony\Component\Scheduler\RecurringMessage;
use Symfony\Component\Scheduler\Schedule;
use Symfony\Component\Scheduler\ScheduleProviderInterface;
use Symfony\Component\Scheduler\Trigger\CronExpressionTrigger;
use Symfony\Component\Scheduler\Trigger\PeriodicalTrigger;
use Xutim\AnalyticsBundle\Message\AggregateAnalyticsMessage;
use Xutim\AnalyticsBundle\Message\ArchiveAnalyticsMessage;

final class AnalyticsScheduleProvider implements ScheduleProviderInterface
{
    public function getSchedule(): Schedule
    {
        return (new Schedule())
            // Hourly: aggregate today for real-time data
            ->add(RecurringMessage::trigger(
                new CronExpressionTrigger(new CronExpression('0 * * * *')),
                new AggregateAnalyticsMessage(new \DateTimeImmutable('today'))
            ))
            // Daily at 2am: finalize yesterday
            ->add(RecurringMessage::trigger(
                new CronExpressionTrigger(new CronExpression('0 2 * * *')),
                new AggregateAnalyticsMessage()
            ))
            // Monthly: archive old events
            ->add(RecurringMessage::trigger(
                new CronExpressionTrigger(new CronExpression('0 3 1 * *')),
                new ArchiveAnalyticsMessage()
            ));
    }
}
```

Register and run the scheduler worker:

```bash
bin/console messenger:consume scheduler_default
```

### Option 2: System Cron

Add to your crontab:

```cron
# Aggregate today's data hourly (for real-time dashboard)
0 * * * * /path/to/bin/console xutim:analytics:aggregate --date=today

# Finalize yesterday's data at 2am
0 2 * * * /path/to/bin/console xutim:analytics:aggregate

# Archive old events monthly
0 3 1 * * /path/to/bin/console xutim:analytics:archive
```

## Real-Time Data

By default, analytics dashboards show data up to yesterday because aggregation runs nightly.

For real-time "today" data, add hourly aggregation. The aggregation is **idempotent**: running it multiple times for the same day safely replaces the data.

### More Frequent Updates

For near real-time data (e.g., every 15 minutes):

```php
// Every 15 minutes: aggregate today
->add(RecurringMessage::trigger(
    new PeriodicalTrigger(900), // 900 seconds = 15 min
    new AggregateAnalyticsMessage(new \DateTimeImmutable('today'))
))
```

Or via cron:

```cron
*/15 * * * * /path/to/bin/console xutim:analytics:aggregate --date=today
```

### Trade-offs

| Frequency | Freshness | Database Load |
|-----------|-----------|---------------|
| Hourly | ~1 hour delay | Low |
| Every 15 min | ~15 min delay | Moderate |
| Every 5 min | ~5 min delay | Higher |

For most sites, hourly aggregation provides good balance between freshness and performance.

## Related

- [Commands](commands.md) - CLI reference for aggregation commands
- [Architecture](architecture.md) - How aggregation works
