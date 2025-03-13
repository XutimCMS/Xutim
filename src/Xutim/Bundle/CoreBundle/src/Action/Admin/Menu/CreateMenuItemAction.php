<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Action\Admin\Menu;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Translation\TranslatorInterface;
use Xutim\CoreBundle\Context\SiteContext;
use Xutim\CoreBundle\Entity\MenuItem;
use Xutim\CoreBundle\Entity\User;
use Xutim\CoreBundle\Form\Admin\Dto\MenuItemDto;
use Xutim\CoreBundle\Form\Admin\MenuItemType;
use Xutim\CoreBundle\Repository\MenuItemRepository;

#[Route('/menu/new-item/{id?null}', name: 'admin_menu_item_new', methods: ['get', 'post'])]
class CreateMenuItemAction extends AbstractController
{
    public function __construct(
        private readonly MenuItemRepository $repo,
        private readonly TranslatorInterface $translator,
        private readonly SiteContext $siteContext
    ) {
    }

    public function __invoke(Request $request, ?MenuItem $parentItem): Response
    {
        $this->denyAccessUnlessGranted(User::ROLE_EDITOR);
        $form = $this->createForm(MenuItemType::class, null, [
            'action' => $this->generateUrl('admin_menu_item_new', $parentItem === null ? [] : [
                'id' => $parentItem->getId()
            ])
        ]);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            /** @var MenuItemDto $data */
            $data = $form->getData();

            $item = new MenuItem($parentItem, $data->hasLink, $data->page, $data->article);
            $this->repo->save($item, true);
            $this->siteContext->resetMenu();

            $this->addFlash('success', $this->translator->trans('flash.changes_made_successfully', [], 'admin'));

            if ($request->headers->has('turbo-frame')) {
                $stream = $this->renderBlockView('@XutimCore/admin/menu/menu_item_new.html.twig', 'stream_success');

                $this->addFlash('stream', $stream);
            }

            return $this->redirectToRoute('admin_menu_list', ['id' => $item->getId()], Response::HTTP_SEE_OTHER);
        }

        return $this->render('@XutimCore/admin/menu/menu_item_new.html.twig', [
            'form' => $form
        ]);
    }
}
