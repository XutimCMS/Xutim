<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Action\Admin\Menu;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Xutim\CoreBundle\Entity\MenuItem;
use Xutim\CoreBundle\Repository\MenuItemRepository;
use Xutim\CoreBundle\Service\MenuBuilder;

#[Route('/menu/{id?null}', name: 'admin_menu_list', methods: ['get'])]
class ShowMenuAction extends AbstractController
{
    public function __construct(
        private readonly MenuItemRepository $repo,
        private readonly MenuBuilder $menuBuilder
    ) {
    }

    public function __invoke(Request $request, ?MenuItem $item): Response
    {
        $hierarchy = $this->menuBuilder->constructHierarchy();
        $path = $item !== null ? $this->repo->getPathHydrated($item) : [];

        return $this->render('@XutimCore/admin/menu/menu.html.twig', [
            'rootItemsId' => $hierarchy['roots'],
            'items' => $hierarchy['items'],
            'selectedItem' => $item,
            'path' => $path
        ]);
    }
}
