# Templates & Routes

## Routes

### Admin Routes

| Route | Path | Description |
|-------|------|-------------|
| `admin_analytics_dashboard` | `/admin/analytics` | Main dashboard |
| `admin_analytics_pages` | `/admin/analytics/pages` | All pages list |
| `admin_analytics_page` | `/admin/analytics/page` | Single page detail |
| `admin_analytics_sources` | `/admin/analytics/sources` | Traffic sources |
| `admin_analytics_geography` | `/admin/analytics/geography` | Countries breakdown |
| `admin_analytics_devices` | `/admin/analytics/devices` | Device types |

### Public Routes

| Route | Path | Description |
|-------|------|-------------|
| `xutim_analytics_collect` | `/_analytics/collect` | Collection endpoint (POST) |

## Templates

Admin templates are located in `@XutimAnalytics/admin/analytics/`:

| Template | Description |
|----------|-------------|
| `dashboard.html.twig` | Main dashboard with charts and summaries |
| `pages.html.twig` | Paginated list of all tracked pages |
| `page_detail.html.twig` | Detailed metrics for a single page |
| `sources.html.twig` | Traffic sources breakdown |
| `geography.html.twig` | Country-based visitor breakdown |
| `devices.html.twig` | Device type breakdown |
| `_date_range_selector.html.twig` | Reusable date picker component |

### CoreBundle Dependency

Admin templates extend `@XutimCore/admin/base.html.twig` and use CoreBundle's:

- Admin layout and navigation
- Breadcrumb component (`<twig:Xutim:Admin:Breadcrumbs>`)
- Tabler CSS framework
- Symfony UX Charts

For standalone usage without CoreBundle, you must provide your own admin templates.

## Controllers

Controllers are called "Actions" and located in `src/Action/`:

| Action | Template | Description |
|--------|----------|-------------|
| `Admin\DashboardAction` | `dashboard.html.twig` | Main dashboard |
| `Admin\PagesListAction` | `pages.html.twig` | Pages list |
| `Admin\SinglePageAction` | `page_detail.html.twig` | Page detail |
| `Admin\TrafficSourcesAction` | `sources.html.twig` | Traffic sources |
| `Admin\GeographyAction` | `geography.html.twig` | Geography |
| `Admin\DevicesAction` | `devices.html.twig` | Devices |
| `Public\CollectAnalyticsAction` | - | Collection endpoint |

## Related

- [Extending](extending.md) - Override templates and routes
- [Installation](installation.md) - Route configuration
