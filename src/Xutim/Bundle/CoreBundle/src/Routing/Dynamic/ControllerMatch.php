<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Routing\Dynamic;

use Symfony\Component\HttpKernel\Event\RequestEvent;

final readonly class ControllerMatch extends DynamicMatch
{
    /**
     * @param array<string, mixed> $attributes
     */
    public function __construct(
        public string $controller,
        public string $name,
        public array $attributes = []
    ) {
    }

    public function apply(RequestEvent $event): void
    {
        $request = $event->getRequest();
        $request->attributes->set('_controller', $this->controller);
        $request->attributes->set('_route', $this->name);
        $request->attributes->add($this->attributes);
    }
}
