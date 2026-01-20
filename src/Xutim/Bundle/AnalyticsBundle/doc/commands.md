# Commands

## xutim:analytics:aggregate

Aggregate raw events into daily summary tables.

```bash
# Aggregate yesterday (default)
bin/console xutim:analytics:aggregate

# Aggregate specific date
bin/console xutim:analytics:aggregate 2024-01-15

# Aggregate today (for real-time data)
bin/console xutim:analytics:aggregate today
```

### Arguments

| Argument | Description | Default |
|----------|-------------|---------|
| `date` | Date to aggregate (YYYY-MM-DD or "today"/"yesterday") | yesterday |

### Notes

- Safe to run multiple times for the same date (idempotent)
- Replaces existing aggregated data for that date
- Used by scheduler for automated aggregation

---

## xutim:analytics:backfill

Backfill aggregation tables from historical raw events.

```bash
# Backfill all historical data
bin/console xutim:analytics:backfill

# Backfill specific date range
bin/console xutim:analytics:backfill --from=2024-01-01 --to=2024-06-30
```

### Options

| Option | Description | Default |
|--------|-------------|---------|
| `--from` | Start date (YYYY-MM-DD) | Earliest event date |
| `--to` | End date (YYYY-MM-DD) | Yesterday |

### Use Cases

- Initial setup after importing existing data
- Recovering from failed aggregation
- Rebuilding aggregates after schema changes

---

## xutim:analytics:archive

Move old raw events to archive table.

```bash
# Archive with default settings (90 days retention)
bin/console xutim:analytics:archive

# Custom retention period
bin/console xutim:analytics:archive --retention-days=30

# Preview without changes
bin/console xutim:analytics:archive --dry-run

# Smaller batches for large datasets
bin/console xutim:analytics:archive --batch-size=5000
```

### Options

| Option | Description | Default |
|--------|-------------|---------|
| `--retention-days` | Days to keep raw events | 90 |
| `--batch-size` | Rows per transaction batch | 10000 |
| `--dry-run` | Preview without changes | false |

### How It Works

1. Finds events older than retention period
2. Copies them to `analytics_event_archive` table in batches
3. Deletes original events
4. Each batch runs in a transaction for safety

### Notes

- Archives are kept for compliance/auditing
- Can purge archives separately if needed
- Use `--dry-run` first on large datasets
- Smaller `--batch-size` reduces memory usage

---

## Related

- [Scheduler](scheduler.md) - Automate command execution
- [Architecture](architecture.md) - Data flow and aggregation
