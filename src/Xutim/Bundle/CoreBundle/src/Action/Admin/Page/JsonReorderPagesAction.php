<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Action\Admin\Page;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Xutim\CoreBundle\Repository\PageRepository;
use Xutim\SecurityBundle\Security\UserRoles;

#[Route('/json/page/move/{id}/{direction}', name: 'admin_json_page_move', requirements: ['direction' => '0|1'], methods: ['post'])]
class JsonReorderPagesAction extends AbstractController
{
    public const MOVE_UP = '0';
    public const MOVE_DOWN = '1';

    public function __construct(private readonly PageRepository $pageRepo)
    {
    }

    public function __invoke(string $id, string $direction): Response
    {
        $page = $this->pageRepo->find($id);
        if ($page === null) {
            throw $this->createNotFoundException('The page does not exist');
        }
        $this->denyAccessUnlessGranted(UserRoles::ROLE_EDITOR);
        if ($direction === self::MOVE_UP) {
            $this->pageRepo->moveUp($page);
        } else {
            $this->pageRepo->moveDown($page);
        }

        return $this->json('OK');
    }
}
