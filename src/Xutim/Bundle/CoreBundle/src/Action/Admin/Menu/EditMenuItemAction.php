<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Action\Admin\Menu;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\Translation\TranslatorInterface;
use Xutim\CoreBundle\Context\SiteContext;
use Xutim\CoreBundle\Form\Admin\Dto\MenuItemDto;
use Xutim\CoreBundle\Form\Admin\MenuItemType;
use Xutim\CoreBundle\Repository\MenuItemRepository;
use Xutim\CoreBundle\Routing\AdminUrlGenerator;
use Xutim\SecurityBundle\Security\UserRoles;

class EditMenuItemAction extends AbstractController
{
    public function __construct(
        private readonly MenuItemRepository $repo,
        private readonly TranslatorInterface $translator,
        private readonly SiteContext $siteContext,
        private readonly AdminUrlGenerator $router
    ) {
    }

    public function __invoke(Request $request, string $id): Response
    {
        $item = $this->repo->find($id);
        if ($item === null) {
            throw $this->createNotFoundException('The menu item does not exist');
        }
        $this->denyAccessUnlessGranted(UserRoles::ROLE_EDITOR);
        $form = $this->createForm(MenuItemType::class, $item->toDto(), [
            'action' => $this->router->generate('admin_menu_item_edit', [
                'id' => $item->getId()
            ])
        ]);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            /** @var MenuItemDto $data */
            $data = $form->getData();

            $item->change(
                $data->hasLink,
                $data->page,
                $data->article,
                $data->tag,
                $data->overwritePage,
                $data->snippetAnchor
            );
            $this->repo->save($item, true);
            $this->siteContext->resetMenu();

            $this->addFlash('success', $this->translator->trans('flash.changes_made_successfully', [], 'admin'));

            if ($request->headers->has('turbo-frame')) {
                $stream = $this->renderBlockView('@XutimCore/admin/menu/menu_item_new.html.twig', 'stream_success');

                $this->addFlash('stream', $stream);
            }

            return new RedirectResponse($this->router->generate('admin_menu_list', ['id' => $item->getId()]), Response::HTTP_SEE_OTHER);
        }

        return $this->render('@XutimCore/admin/menu/menu_item_new.html.twig', [
            'form' => $form,
            'item' => $item
        ]);
    }
}
