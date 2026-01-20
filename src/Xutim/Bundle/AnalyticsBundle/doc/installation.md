# Installation

## With XutimCMS

If using XutimCMS, the bundle integrates automatically:

- Admin dashboard at `/admin/analytics`
- Automatic scheduler for aggregation
- Pre-configured entity models

Skip to [Entity Setup](#entity-setup).

## Standalone Installation

### 1. Install

```bash
composer require xutim/analytics-bundle
```

### 2. Register Bundle

```php
// config/bundles.php
return [
    // ...
    Xutim\AnalyticsBundle\XutimAnalyticsBundle::class => ['all' => true],
];
```

### 3. Entity Setup

Create concrete entity classes extending the bundle's MappedSuperclass entities:

```php
// src/Entity/AnalyticsEvent.php
namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Xutim\AnalyticsBundle\Entity\AnalyticsEvent as BaseAnalyticsEvent;

#[ORM\Entity]
#[ORM\Table(name: 'analytics_event')]
class AnalyticsEvent extends BaseAnalyticsEvent
{
}
```

Required entities:

- `AnalyticsEvent` - Raw pageview events
- `AnalyticsEventArchive` - Archived events (older than retention period)
- `AnalyticsDailySummary` - Daily totals
- `AnalyticsDailyTrafficSource` - Traffic sources per day
- `AnalyticsDailyCountry` - Countries per day
- `AnalyticsDailyDevice` - Devices per day
- `AnalyticsDailyUtm` - UTM parameters per day
- `AnalyticsDailyPageReferrer` - Page/referrer combinations per day
- `AnalyticsDailySession` - Session metrics per day

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

### 5. Import Routes

```yaml
# config/routes/xutim_analytics.yaml
xutim_analytics:
    resource: '@XutimAnalyticsBundle/config/routes.php'
```

### 6. Run Migrations

```bash
bin/console doctrine:migrations:diff
bin/console doctrine:migrations:migrate
```

## Tracking Setup

The bundle provides a Stimulus controller for tracking. It collects pageviews, scroll depth, and click interactions.

### With Symfony UX (Recommended)

Register the controller in your `assets/controllers.json`:

```json
{
    "controllers": {
        "@xutimcms/analytics-bundle": {
            "analytics": {
                "enabled": true,
                "fetch": "eager"
            }
        }
    }
}
```

Add the controller to your base layout:

```html
<body data-controller="xutimcms--analytics-bundle--analytics"></body>
```

Or with Twig helper:

```twig
<body {{ stimulus_controller('@xutimcms/analytics-bundle/analytics') }}>
```

### How It Works

The controller:

1. Waits 2 seconds after page load
2. Collects: path, screen size, load time, scroll depth, clicks
3. Sends data via `navigator.sendBeacon` to `/_analytics/collect`
4. Cleans up event listeners

No cookies are used. IP addresses are anonymized server-side.

## Next Steps

- [Configuration](configuration.md) - All configuration options
- [Scheduler](scheduler.md) - Set up automatic aggregation
- [Commands](commands.md) - CLI reference
