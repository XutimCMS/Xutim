# Configuration

## Full Configuration Reference

```yaml
xutim_analytics:
    # Your site's primary host for internal/external referrer detection
    # Falls back to Request::getHost() if not set
    site_host: 'example.com'

    # Entity model mappings (required)
    models:
        analytics_event:
            class: App\Entity\AnalyticsEvent
        analytics_event_archive:
            class: App\Entity\AnalyticsEventArchive
        analytics_daily_summary:
            class: App\Entity\AnalyticsDailySummary
        analytics_daily_traffic_source:
            class: App\Entity\AnalyticsDailyTrafficSource
        analytics_daily_country:
            class: App\Entity\AnalyticsDailyCountry
        analytics_daily_device:
            class: App\Entity\AnalyticsDailyDevice
        analytics_daily_utm:
            class: App\Entity\AnalyticsDailyUtm
        analytics_daily_page_referrer:
            class: App\Entity\AnalyticsDailyPageReferrer
        analytics_daily_session:
            class: App\Entity\AnalyticsDailySession
```

## Options

### `site_host`

Used to distinguish internal navigation from external referrers. When a visitor arrives from a page on the same host, it's not counted as a referrer.

If not set, the bundle uses `Request::getHost()` which works for single-domain sites. Set this explicitly for:

- Sites with multiple domains
- Sites behind a reverse proxy

### `models`

Maps entity aliases to your concrete entity classes. All models are required.

Each entity must extend the corresponding base class from the bundle:

| Alias | Base Class |
|-------|------------|
| `analytics_event` | `Xutim\AnalyticsBundle\Entity\AnalyticsEvent` |
| `analytics_event_archive` | `Xutim\AnalyticsBundle\Entity\AnalyticsEventArchive` |
| `analytics_daily_summary` | `Xutim\AnalyticsBundle\Entity\AnalyticsDailySummary` |
| `analytics_daily_traffic_source` | `Xutim\AnalyticsBundle\Entity\AnalyticsDailyTrafficSource` |
| `analytics_daily_country` | `Xutim\AnalyticsBundle\Entity\AnalyticsDailyCountry` |
| `analytics_daily_device` | `Xutim\AnalyticsBundle\Entity\AnalyticsDailyDevice` |
| `analytics_daily_utm` | `Xutim\AnalyticsBundle\Entity\AnalyticsDailyUtm` |
| `analytics_daily_page_referrer` | `Xutim\AnalyticsBundle\Entity\AnalyticsDailyPageReferrer` |
| `analytics_daily_session` | `Xutim\AnalyticsBundle\Entity\AnalyticsDailySession` |

## Messenger Routing

The bundle automatically routes `CollectAnalyticsMessage` to the `async` transport. To change this, configure it in your Messenger config:

```yaml
# config/packages/messenger.yaml
framework:
    messenger:
        routing:
            Xutim\AnalyticsBundle\Message\CollectAnalyticsMessage: sync
```

## Related

- [Installation](installation.md) - Entity setup
- [Scheduler](scheduler.md) - Aggregation scheduling
- [Architecture](architecture.md) - How entities work together
