<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Action\Admin\Page;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Xutim\CoreBundle\Repository\PageRepository;

#[Route('/json/page/list', name: 'admin_json_page_list', methods: ['get'])]
class JsonListPagesAction extends AbstractController
{
    public function __construct(
        private readonly PageRepository $pageRepo
    ) {
    }

    public function __invoke(): JsonResponse
    {
        $paths = $this->pageRepo->findAllPaths();
        
        return $this->json($paths);
    }
}
