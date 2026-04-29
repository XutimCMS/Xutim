<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Routing\Dynamic;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\RequestEvent;

final readonly class ResponseMatch extends DynamicMatch
{
    public function __construct(
        public Response $response,
        public string $name
    ) {
    }

    public function apply(RequestEvent $event): void
    {
        $event->setResponse($this->response);
    }
}
