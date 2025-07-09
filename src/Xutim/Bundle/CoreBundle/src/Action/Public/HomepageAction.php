<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Action\Public;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Xutim\CoreBundle\Twig\ThemeFinder;

#[Route(
    '/{_locale?en}/',
    name: 'homepage',
    requirements: ['_locale' => 'en|fr']
)]
class HomepageAction extends AbstractController
{
    public function __invoke(ThemeFinder $themeFinder): Response
    {
        return $this->render($themeFinder->getActiveThemePath('/homepage/homepage.html.twig'));
    }
}
