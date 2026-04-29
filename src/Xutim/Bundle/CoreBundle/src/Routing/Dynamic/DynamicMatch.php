<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Routing\Dynamic;

use Symfony\Component\HttpKernel\Event\RequestEvent;

abstract readonly class DynamicMatch
{
    abstract public function apply(RequestEvent $event): void;
}
