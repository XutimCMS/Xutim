<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Action\Admin;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Xutim\CoreBundle\Routing\AdminUrlGenerator;

class AdminHomepageRedirectAction extends AbstractController
{
    public function __construct(private readonly AdminUrlGenerator $router)
    {
    }

    public function __invoke(): Response
    {
        return new RedirectResponse($this->router->generate('admin_homepage', ['_content_locale' => 'en']));
    }
}
