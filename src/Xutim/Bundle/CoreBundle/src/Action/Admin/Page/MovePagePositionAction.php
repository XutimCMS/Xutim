<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Action\Admin\Page;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Xutim\CoreBundle\Repository\PageRepository;
use Xutim\CoreBundle\Routing\AdminUrlGenerator;
use Xutim\SecurityBundle\Security\UserRoles;

class MovePagePositionAction extends AbstractController
{
    public function __construct(
        private readonly PageRepository $pageRepo,
        private readonly AdminUrlGenerator $router,
    ) {
    }

    public function __invoke(string $id, string $dir): Response
    {
        $page = $this->pageRepo->find($id);
        if ($page === null) {
            throw $this->createNotFoundException('The page does not exist');
        }
        $this->denyAccessUnlessGranted(UserRoles::ROLE_EDITOR);
        if ($dir === 'up') {
            $this->pageRepo->moveUp($page);
        } elseif ($dir === 'down') {
            $this->pageRepo->moveDown($page);
        } else {
            throw new NotFoundHttpException('Invalid direction');
        }
        $this->pageRepo->save($page, true);

        if ($page->getParent() === null) {
            return new RedirectResponse($this->router->generate('admin_page_list'));
        }

        return new RedirectResponse($this->router->generate('admin_page_list', ['id' => $page->getParent()->getId()]));
    }
}
