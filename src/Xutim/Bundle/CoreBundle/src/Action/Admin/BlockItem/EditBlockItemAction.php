<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Action\Admin\BlockItem;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Translation\TranslatorInterface;
use Xutim\CoreBundle\Context\BlockContext;
use Xutim\CoreBundle\Domain\Model\BlockItemInterface;
use Xutim\CoreBundle\Entity\User;
use Xutim\CoreBundle\Form\Admin\ArticleBlockItemType;
use Xutim\CoreBundle\Form\Admin\Dto\ArticleBlockItemDto;
use Xutim\CoreBundle\Form\Admin\Dto\PageBlockItemDto;
use Xutim\CoreBundle\Form\Admin\Dto\SimpleBlockDto;
use Xutim\CoreBundle\Form\Admin\PageBlockItemType;
use Xutim\CoreBundle\Form\Admin\SimpleBlockItemType;
use Xutim\CoreBundle\Repository\BlockItemRepository;

class EditBlockItemAction extends AbstractController
{
    public function __construct(
        private readonly BlockItemRepository $blockItemRepository,
        private readonly TranslatorInterface $translator,
        private readonly BlockContext $blockContext
    ) {
    }

    #[Route('/block/edit-article/{id}', name: 'admin_block_edit_article')]
    public function addArticleAction(Request $request, string $id): Response
    {
        $item = $this->blockItemRepository->find($id);
        if ($item === null) {
            throw $this->createNotFoundException('The item does not exist');
        }
        $data = $item->getDto();
        $form = $this->createForm(ArticleBlockItemType::class, $data, [
            'action' => $this->generateUrl('admin_block_edit_article', ['id' => $item->getId()])
        ]);

        return $this->executeAction($request, $item, $form);
    }

    #[Route('/block/edit-page/{id}', name: 'admin_block_edit_page')]
    public function addPageAction(Request $request, string $id): Response
    {
        $item = $this->blockItemRepository->find($id);
        if ($item === null) {
            throw $this->createNotFoundException('The item does not exist');
        }
        $data = $item->getDto();
        $form = $this->createForm(PageBlockItemType::class, $data, [
            'action' => $this->generateUrl('admin_block_edit_page', ['id' => $item->getId()])
        ]);

        return $this->executeAction($request, $item, $form);
    }

    #[Route('/block/edit-simple-item/{id}', name: 'admin_block_edit_simple_item')]
    public function addSimpleItemAction(Request $request, string $id): Response
    {
        $item = $this->blockItemRepository->find($id);
        if ($item === null) {
            throw $this->createNotFoundException('The item does not exist');
        }
        $data = $item->getDto();
        $form = $this->createForm(SimpleBlockItemType::class, $data, [
            'action' => $this->generateUrl('admin_block_edit_simple_item', ['id' => $item->getId()])
        ]);

        return $this->executeAction($request, $item, $form);
    }

    /**
     * @param FormInterface<SimpleBlockDto|PageBlockItemDto|ArticleBlockItemDto> $form
     */
    private function executeAction(Request $request, BlockItemInterface $item, FormInterface $form): Response
    {
        $this->denyAccessUnlessGranted(User::ROLE_EDITOR);
        $block = $item->getBlock();
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $dto = $data->toBlockItemDto();

            $item->change(
                $dto->page,
                $dto->article,
                $dto->file,
                $dto->snippet,
                $dto->tag,
                $dto->link,
                $dto->color,
                $dto->fileDescription,
                $dto->coordinates?->latitude,
                $dto->coordinates?->longitude
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
