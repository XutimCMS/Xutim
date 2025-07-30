<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Action\Public;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Xutim\CoreBundle\Twig\ThemeFinder;

class HomepageAction extends AbstractController
{
    public function __invoke(ThemeFinder $themeFinder): Response
    {
        return $this->render($themeFinder->getActiveThemePath('/homepage/homepage.html.twig'));
    }
}
