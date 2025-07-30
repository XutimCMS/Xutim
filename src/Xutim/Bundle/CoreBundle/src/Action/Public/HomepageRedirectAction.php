<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Action\Public;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class HomepageRedirectAction
{
    public function __construct(private readonly UrlGeneratorInterface $router)
    {
    }

    public function __invoke(): Response
    {
        return new RedirectResponse($this->router->generate('homepage', ['_locale' => 'en']));
    }
}
