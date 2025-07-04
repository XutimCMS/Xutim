<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Action\Admin\BlockItem;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Translation\TranslatorInterface;
use Xutim\CoreBundle\Config\Layout\Block\BlockLayoutChecker;
use Xutim\CoreBundle\Context\BlockContext;
use Xutim\CoreBundle\Form\Admin\BlockItemType;
use Xutim\CoreBundle\Form\Admin\Dto\BlockItemDto;
use Xutim\CoreBundle\Repository\BlockItemRepository;
use Xutim\SecurityBundle\Security\UserRoles;

class EditBlockItemAction extends AbstractController
{
    public function __construct(
        private readonly BlockItemRepository $blockItemRepository,
        private readonly TranslatorInterface $translator,
        private readonly BlockContext $blockContext,
        private readonly BlockLayoutChecker $blockLayoutChecker
    ) {
    }

    #[Route('/block/edit-item/{id}', name: 'admin_block_edit_item')]
    public function addItemAction(Request $request, string $id): Response
    {
        $item = $this->blockItemRepository->find($id);
        if ($item === null) {
            throw $this->createNotFoundException('The item does not exist');
        }

        $form = $this->createForm(BlockItemType::class, $item->getDto(), [
            'action' => $this->generateUrl('admin_block_edit_item', ['id' => $item->getId()]),
            'block_options' => $this->blockLayoutChecker->extractAllowedOptions($item->getBlock())
        ]);

        $this->denyAccessUnlessGranted(UserRoles::ROLE_EDITOR);
        $block = $item->getBlock();
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var BlockItemDto $data */
            $data = $form->getData();

            $item->change(
                $data->page,
                $data->article,
                $data->file,
                $data->snippet,
                $data->tag,
                $data->text,
                $data->link,
                $data->color,
                $data->fileDescription,
                $data->coordinates?->latitude,
                $data->coordinates?->longitude
            );

            $this->blockItemRepository->save($item, true);
            $this->blockContext->resetAllLocalesBlockTemplate($block->getCode());

            $this->addFlash('success', $this->translator->trans('flash.changes_made_successfully', [], 'admin'));

            if ($request->headers->has('turbo-frame')) {
                $stream = $this->renderBlockView('@XutimCore/admin/block/block_item_edit_form.html.twig', 'stream_success', [
                    'block' => $block,
                    'item' => $item
                ]);

                $this->addFlash('stream', $stream);
            }

            return $this->redirectToRoute('admin_block_show', ['id' => $block->getId()], Response::HTTP_SEE_OTHER);
        }

        return $this->render('@XutimCore/admin/block/block_item_edit_form.html.twig', [
            'form' => $form,
            'block' => $block,
            'item' => $item
        ]);
    }
}
