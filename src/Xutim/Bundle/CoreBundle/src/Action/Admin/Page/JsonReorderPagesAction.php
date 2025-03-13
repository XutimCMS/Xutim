<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Action\Admin\Page;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Xutim\CoreBundle\Entity\Page;
use Xutim\CoreBundle\Entity\User;
use Xutim\CoreBundle\Repository\PageRepository;

#[Route('/json/page/move/{id}/{direction}', name: 'admin_json_page_move', requirements: ['direction' => '0|1'], methods: ['post'])]
class JsonReorderPagesAction extends AbstractController
{
    public const MOVE_UP = '0';
    public const MOVE_DOWN = '1';

    public function __construct(private readonly PageRepository $pageRepository)
    {
    }

    public function __invoke(Page $page, string $direction): Response
    {
        $this->denyAccessUnlessGranted(User::ROLE_EDITOR);
        if ($direction === self::MOVE_UP) {
            $this->pageRepository->moveUp($page);
        } else {
            $this->pageRepository->moveDown($page);
        }

        return $this->json('OK');
    }
}
