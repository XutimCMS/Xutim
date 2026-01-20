# Architecture

## Data Flow

```
Browser → Collect Endpoint → Raw Events → Aggregation → Daily Tables
                                              ↓
                                          Archive
```

1. **Collection**: JavaScript tracker sends pageview data to `/_analytics/collect`
2. **Storage**: Raw events stored in `analytics_event` table
3. **Aggregation**: Daily job processes raw events into summary tables
4. **Archival**: Monthly job moves old events to archive table

## Entity Types

### Raw Events

**AnalyticsEvent** - Individual pageview records

- Stored immediately on each pageview
- Contains: path, referrer, UTM params, device info, country, timestamp
- Used for aggregation, then archived
- High volume, short retention

### Aggregated Data

Daily summary tables pre-compute metrics for fast dashboard queries:

| Entity | Purpose |
|--------|---------|
| `AnalyticsDailySummary` | Daily totals (visitors, pageviews, bounce rate) |
| `AnalyticsDailyTrafficSource` | Visitors per traffic source per day |
| `AnalyticsDailyCountry` | Visitors per country per day |
| `AnalyticsDailyDevice` | Visitors per device type per day |
| `AnalyticsDailyUtm` | UTM campaign metrics per day |
| `AnalyticsDailyPageReferrer` | Page/referrer combinations per day |
| `AnalyticsDailySession` | Session duration, pages per session |

### Archive

**AnalyticsEventArchive** - Archived raw events

- Same structure as `AnalyticsEvent`
- Events older than retention period moved here
- Keeps raw data for compliance/auditing
- Can be purged independently

## Why DBAL for Aggregation

Aggregation uses raw DBAL queries instead of Doctrine ORM:

```sql
INSERT INTO analytics_daily_summary (date, visitors, pageviews, ...)
SELECT DATE(created_at), COUNT(DISTINCT session_id), COUNT(*), ...
FROM analytics_event
WHERE DATE(created_at) = :date
GROUP BY DATE(created_at)
```

Benefits:
- **Performance**: Single query processes all events for a day
- **Atomicity**: Delete + insert in transaction ensures consistency
- **Idempotency**: Re-running aggregation safely replaces existing data

## Idempotent Aggregation

Running aggregation multiple times for the same day is safe:

```php
$this->deleteByDate($date);  // Remove existing aggregated data
$this->insertAggregatedData($date);  // Insert fresh aggregation
```

This enables:
- Hourly re-aggregation for real-time data
- Easy recovery from errors
- Backfilling historical data

## Session Tracking

Sessions are tracked without cookies using a hash of:
- IP address (anonymized)
- User agent
- Date

Same visitor on same day = same session. Different day = new session.

## Privacy

- **No cookies**: Session identification via fingerprint hash
- **IP anonymization**: Last octet zeroed before storage
- **No PII**: Only aggregate behavioral data stored
- **Self-hosted**: All data stays on your server

## Related

- [Commands](commands.md) - Aggregation and archive commands
- [Extending](extending.md) - Custom entity fields
