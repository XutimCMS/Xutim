<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Action\Admin;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;

class DesignTestAction extends AbstractController
{
    public function __invoke(): Response
    {
        return $this->render('@XutimCore/admin/design_test.html.twig');
    }
}
