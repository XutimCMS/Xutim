<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Action\Public;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Xutim\CoreBundle\Context\SnippetsContext;
use Xutim\CoreBundle\Twig\ThemeFinder;

#[Route('/{_locale?en}', name: 'homepage')]
class HomepageAction extends AbstractController
{
    public function __invoke(ThemeFinder $themeFinder, SnippetsContext $context): Response
    {
        return $this->render($themeFinder->getActiveThemePath('/homepage/homepage.html.twig'));
    }
}
