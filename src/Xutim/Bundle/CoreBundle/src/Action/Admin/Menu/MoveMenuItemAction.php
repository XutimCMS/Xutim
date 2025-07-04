<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Action\Admin\Menu;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Attribute\Route;
use Xutim\CoreBundle\Context\SiteContext;
use Xutim\CoreBundle\Repository\MenuItemRepository;
use Xutim\SecurityBundle\Security\UserRoles;

#[Route('/menu/{id}/move/{dir}', name: 'admin_menu_item_move')]
class MoveMenuItemAction extends AbstractController
{
    public function __construct(
        private readonly MenuItemRepository $repo,
        private readonly SiteContext $siteContext
    ) {
    }

    public function __invoke(string $id, string $dir): Response
    {
        $item = $this->repo->find($id);
        if ($item === null) {
            throw $this->createNotFoundException('The menu item does not exist');
        }
        $this->denyAccessUnlessGranted(UserRoles::ROLE_EDITOR);
        if ($dir === 'up') {
            $this->repo->moveUp($item);
        } elseif ($dir === 'down') {
            $this->repo->moveDown($item);
        } else {
            throw new NotFoundHttpException('Invalid direction');
        }
        $this->repo->save($item, true);
        $this->siteContext->resetMenu();

        if ($item->getParent() === null) {
            return $this->redirectToRoute('admin_menu_list');
        }

        return $this->redirectToRoute('admin_menu_list', [
            'id' => $item->getParent()->getId()
        ]);
    }
}
