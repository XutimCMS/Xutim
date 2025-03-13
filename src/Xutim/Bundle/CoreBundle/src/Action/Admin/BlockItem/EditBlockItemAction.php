<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Action\Admin\BlockItem;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Translation\TranslatorInterface;
use Xutim\CoreBundle\Context\BlockContext;
use Xutim\CoreBundle\Entity\BlockItem;
use Xutim\CoreBundle\Entity\User;
use Xutim\CoreBundle\Form\Admin\ArticleBlockItemType;
use Xutim\CoreBundle\Form\Admin\Dto\ArticleBlockItemDto;
use Xutim\CoreBundle\Form\Admin\Dto\PageBlockItemDto;
use Xutim\CoreBundle\Form\Admin\Dto\SimpleBlockDto;
use Xutim\CoreBundle\Form\Admin\PageBlockItemType;
use Xutim\CoreBundle\Form\Admin\SimpleBlockItemType;
use Xutim\CoreBundle\Message\Command\File\UploadFileMessage;
use Xutim\CoreBundle\Repository\BlockItemRepository;
use Xutim\CoreBundle\Repository\FileRepository;
use Xutim\CoreBundle\Security\UserStorage;
use Xutim\CoreBundle\Service\FileService;

class EditBlockItemAction extends AbstractController
{
    public function __construct(
        private readonly FileRepository $fileRepository,
        private readonly BlockItemRepository $blockItemRepository,
        private readonly UserStorage $userStorage,
        private readonly TranslatorInterface $translator,
        private readonly MessageBusInterface $commandBus,
        private readonly FileService $fileService,
        private readonly BlockContext $blockContext
    ) {
    }

    #[Route('/block/edit-article/{id}', name: 'admin_block_edit_article')]
    public function addArticleAction(Request $request, BlockItem $item): Response
    {
        $data = $item->getDto($this->getFile($item));
        $form = $this->createForm(ArticleBlockItemType::class, $data, [
            'action' => $this->generateUrl('admin_block_edit_article', ['id' => $item->getId()])
        ]);

        return $this->executeAction($request, $item, $form);
    }

    #[Route('/block/edit-page/{id}', name: 'admin_block_edit_page')]
    public function addPageAction(Request $request, BlockItem $item): Response
    {
        $data = $item->getDto($this->getFile($item));
        $form = $this->createForm(PageBlockItemType::class, $data, [
            'action' => $this->generateUrl('admin_block_edit_page', ['id' => $item->getId()])
        ]);

        return $this->executeAction($request, $item, $form);
    }

    #[Route('/block/edit-simple-item/{id}', name: 'admin_block_edit_simple_item')]
    public function addSimpleItemAction(Request $request, BlockItem $item): Response
    {
        $data = $item->getDto($this->getFile($item));
        $form = $this->createForm(SimpleBlockItemType::class, $data, [
            'action' => $this->generateUrl('admin_block_edit_simple_item', ['id' => $item->getId()])
        ]);

        return $this->executeAction($request, $item, $form);
    }

    /**
     * @param FormInterface<SimpleBlockDto|PageBlockItemDto|ArticleBlockItemDto> $form
     */
    private function executeAction(Request $request, BlockItem $item, FormInterface $form): Response
    {
        $this->denyAccessUnlessGranted(User::ROLE_EDITOR);
        $block = $item->getBlock();
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $dto = $data->toBlockItemDto();

            $file = $item->getFile();
            if ($dto->file instanceof UploadedFile) {
                $command = new UploadFileMessage(
                    $dto->file,
                    $this->userStorage->getUserWithException()->getUserIdentifier(),
                    null,
                    null,
                    '',
                    '',
                    'en',
                );
                $this->commandBus->dispatch($command);
                $file = $this->fileRepository->find($command->id);
            }

            $item->change(
                $dto->page,
                $dto->article,
                $file,
                $dto->snippet,
                $dto->position,
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

    private function getFile(BlockItem $item): ?\Symfony\Component\HttpFoundation\File\File
    {
        return $this->fileService->createFile($item->getFile());
    }
}
