<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Action\Admin\Menu;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Xutim\CoreBundle\Context\SiteContext;
use Xutim\CoreBundle\Repository\MenuItemRepository;
use Xutim\CoreBundle\Routing\AdminUrlGenerator;
use Xutim\SecurityBundle\Security\UserRoles;

class MoveMenuItemAction extends AbstractController
{
    public function __construct(
        private readonly MenuItemRepository $repo,
        private readonly SiteContext $siteContext,
        private readonly AdminUrlGenerator $router
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
            return new RedirectResponse($this->router->generate('admin_menu_list'));
        }

        return new RedirectResponse($this->router->generate('admin_menu_list', [
            'id' => $item->getParent()->getId()
        ]));
    }
}
