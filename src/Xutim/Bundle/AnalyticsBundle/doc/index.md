# AnalyticsBundle

Privacy-focused analytics for Symfony applications. Track pageviews, visitors, traffic sources, and user behavior without third-party services.

## Features

- **Privacy-first** - No cookies, anonymized IPs, GDPR-friendly
- **Self-hosted** - All data stays on your server
- **Real-time capable** - Configurable aggregation frequency
- **Lightweight** - Minimal JavaScript tracker (~2KB)
- **Extensible** - Custom entities and metrics

## Quick Start

### 1. Install

```bash
composer require xutim/analytics-bundle
```

### 2. Register bundle (if not using Symfony Flex)

```php
// config/bundles.php
return [
    // ...
    Xutim\AnalyticsBundle\XutimAnalyticsBundle::class => ['all' => true],
];
```

### 3. Create entity classes

The bundle requires you to create concrete entity classes. See [Installation](installation.md) for entity setup.

### 4. Configure

```yaml
# config/packages/xutim_analytics.yaml
xutim_analytics:
    site_host: 'example.com'
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

### 5. Run migrations

```bash
bin/console doctrine:migrations:migrate
```

### 6. Add tracking

See [Installation](installation.md#tracking-setup) for Stimulus controller setup.

### 7. Aggregate data

```bash
bin/console xutim:analytics:aggregate
```

## Documentation

- [Installation](installation.md) - Detailed setup for XutimCMS and standalone
- [Configuration](configuration.md) - All configuration options
- [Scheduler](scheduler.md) - Automatic aggregation and real-time data
- [Architecture](architecture.md) - Entity types and data flow
- [Templates](templates.md) - Admin routes and templates
- [Commands](commands.md) - CLI reference
- [Extending](extending.md) - Custom entities, templates, and routes
