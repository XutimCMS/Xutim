# Extending

## Extending Entities

The bundle uses MappedSuperclass entities that you extend in your application. You can add custom fields to your concrete classes.

### Adding Fields to AnalyticsEvent

```php
// src/Entity/AnalyticsEvent.php
namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Xutim\AnalyticsBundle\Domain\Model\AnalyticsEvent as BaseAnalyticsEvent;

#[ORM\Entity]
#[ORM\Table(name: 'analytics_event')]
class AnalyticsEvent extends BaseAnalyticsEvent
{
    #[ORM\Column(type: 'string', nullable: true)]
    private ?string $userId = null;

    #[ORM\Column(type: 'string', nullable: true)]
    private ?string $experimentVariant = null;

    public function getUserId(): ?string
    {
        return $this->userId;
    }

    public function setUserId(?string $userId): void
    {
        $this->userId = $userId;
    }

    public function getExperimentVariant(): ?string
    {
        return $this->experimentVariant;
    }

    public function setExperimentVariant(?string $variant): void
    {
        $this->experimentVariant = $variant;
    }
}
```

### Adding Fields to Aggregation Entities

Same pattern applies to daily aggregation entities:

```php
// src/Entity/AnalyticsDailySummary.php
namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Xutim\AnalyticsBundle\Domain\Model\AnalyticsDailySummary as BaseSummary;

#[ORM\Entity]
#[ORM\Table(name: 'analytics_daily_summary')]
class AnalyticsDailySummary extends BaseSummary
{
    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private int $loggedInVisitors = 0;

    public function getLoggedInVisitors(): int
    {
        return $this->loggedInVisitors;
    }

    public function setLoggedInVisitors(int $count): void
    {
        $this->loggedInVisitors = $count;
    }
}
```

## Custom Factory

To populate custom fields during event collection, extend the factory:

```php
// src/Analytics/CustomAnalyticsEventFactory.php
namespace App\Analytics;

use Symfony\Component\HttpFoundation\Request;
use Xutim\AnalyticsBundle\Domain\Factory\AnalyticsEventFactory;
use Xutim\AnalyticsBundle\Domain\Model\AnalyticsEventInterface;

class CustomAnalyticsEventFactory extends AnalyticsEventFactory
{
    public function createFromRequest(Request $request, array $payload): AnalyticsEventInterface
    {
        $event = parent::createFromRequest($request, $payload);

        if ($event instanceof \App\Entity\AnalyticsEvent) {
            // Add custom data from request or payload
            $event->setUserId($request->headers->get('X-User-ID'));
        }

        return $event;
    }
}
```

Register the custom factory:

```yaml
# config/services.yaml
services:
    App\Analytics\CustomAnalyticsEventFactory:
        decorates: Xutim\AnalyticsBundle\Domain\Factory\AnalyticsEventFactoryInterface
```

## Custom Aggregation

To aggregate custom fields, extend the aggregation service:

```php
// src/Analytics/CustomAggregationService.php
namespace App\Analytics;

use Xutim\AnalyticsBundle\Service\AnalyticsAggregationService;

class CustomAggregationService extends AnalyticsAggregationService
{
    public function aggregateDailySummary(\DateTimeImmutable $date): int
    {
        // Call parent aggregation first
        $count = parent::aggregateDailySummary($date);

        // Add custom aggregation logic
        $this->aggregateLoggedInVisitors($date);

        return $count;
    }

    private function aggregateLoggedInVisitors(\DateTimeImmutable $date): void
    {
        // Custom SQL to aggregate logged-in visitors
    }
}
```

## Custom Repository Methods

Extend repositories for custom queries:

```php
// src/Repository/AnalyticsEventRepository.php
namespace App\Repository;

use Xutim\AnalyticsBundle\Repository\AnalyticsEventRepository as BaseRepository;

class AnalyticsEventRepository extends BaseRepository
{
    public function findByUserId(string $userId): array
    {
        return $this->createQueryBuilder('e')
            ->where('e.userId = :userId')
            ->setParameter('userId', $userId)
            ->getQuery()
            ->getResult();
    }
}
```

## Overriding Templates

Override bundle templates by creating files in your application's `templates/bundles/` directory:

```
templates/bundles/XutimAnalyticsBundle/admin/analytics/dashboard.html.twig
```

Example custom dashboard:

```twig
{% extends '@XutimAnalytics/admin/analytics/dashboard.html.twig' %}

{% block pageTitle %}My Custom Analytics{% endblock %}

{% block body %}
    <div class="alert alert-info">Custom banner here</div>
    {{ parent() }}
{% endblock %}
```

### Standalone Templates (Without CoreBundle)

Admin templates extend `@XutimCore/admin/base.html.twig`. For standalone use, create your own base template:

```twig
{# templates/bundles/XutimAnalyticsBundle/admin/analytics/dashboard.html.twig #}
{% extends 'base.html.twig' %}

{% block body %}
    {# Render your own dashboard layout #}
{% endblock %}
```

## Overriding Routes

Override routes by defining the same route name in your application with higher priority:

```php
// config/routes/analytics.php
use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;

return function (RoutingConfigurator $routes) {
    // Custom prefix for admin routes
    $routes->add('admin_analytics_dashboard', '/my-admin/stats')
        ->controller(App\Controller\CustomDashboardController::class);
};
```

Ensure your routes file loads before the bundle routes, or use route resource ordering.

## Overriding Controllers

Decorate controllers to extend functionality:

```php
// src/Controller/CustomDashboardAction.php
namespace App\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Xutim\AnalyticsBundle\Action\Admin\DashboardAction;

class CustomDashboardAction
{
    public function __construct(
        private DashboardAction $inner,
    ) {}

    public function __invoke(Request $request): Response
    {
        // Add custom logic before/after
        return $this->inner->__invoke($request);
    }
}
```

Register as decorator:

```yaml
# config/services.yaml
services:
    App\Controller\CustomDashboardAction:
        decorates: Xutim\AnalyticsBundle\Action\Admin\DashboardAction
```

## Related

- [Templates](templates.md) - Available templates and routes
- [Architecture](architecture.md) - Entity structure and data flow
- [Installation](installation.md) - Entity setup
