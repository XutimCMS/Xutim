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
use Webmozart\Assert\Assert;
use Xutim\CoreBundle\Context\BlockContext;
use Xutim\CoreBundle\Entity\Block;
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

class AddBlockItemAction extends AbstractController
{
    public function __construct(
        private readonly FileRepository $fileRepository,
        private readonly BlockItemRepository $blockItemRepository,
        private readonly UserStorage $userStorage,
        private readonly TranslatorInterface $translator,
        private readonly MessageBusInterface $commandBus,
        private readonly BlockContext $blockContext
    ) {
    }

    #[Route('/block/add-article/{id}', name: 'admin_block_add_article')]
    public function addArticleAction(Request $request, Block $block): Response
    {
        $form = $this->createForm(ArticleBlockItemType::class, null, [
            'action' => $this->generateUrl('admin_block_add_article', ['id' => $block->getId()])
        ]);

        return $this->executeAction($request, $block, $form);
    }

    #[Route('/block/add-page/{id}', name: 'admin_block_add_page')]
    public function addPageAction(Request $request, Block $block): Response
    {
        $form = $this->createForm(PageBlockItemType::class, null, [
            'action' => $this->generateUrl('admin_block_add_page', ['id' => $block->getId()])
        ]);

        return $this->executeAction($request, $block, $form);
    }

    #[Route('/block/add-simple-item/{id}', name: 'admin_block_add_simple_item')]
    public function addSimpleItemAction(Request $request, Block $block): Response
    {
        $form = $this->createForm(SimpleBlockItemType::class, null, [
            'action' => $this->generateUrl('admin_block_add_simple_item', ['id' => $block->getId()])
        ]);

        return $this->executeAction($request, $block, $form);
    }

    /**
     * @param FormInterface<SimpleBlockDto|null>|FormInterface<PageBlockItemDto|null>|FormInterface<ArticleBlockItemDto|null> $form
     */
    private function executeAction(Request $request, Block $block, FormInterface $form): Response
    {
        $this->denyAccessUnlessGranted(User::ROLE_EDITOR);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            Assert::notNull($data);
            $dto = $data->toBlockItemDto();

            $file = null;
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

            $blockItem = new BlockItem(
                $block,
                $dto->page,
                $dto->article,
                $file,
                $dto->snippet,
                $dto->position,
                $dto->link,
                $dto->color,
                $dto->fileDescription,
                $dto->coordinates?->latitude,
                $dto->coordinates?->longitude,
            );
            $this->blockItemRepository->save($blockItem, true);
            $this->blockContext->resetAllLocalesBlockTemplate($block->getCode());

            $this->addFlash('success', $this->translator->trans('flash.changes_made_successfully', [], 'admin'));

            if ($request->headers->has('turbo-frame')) {
                $stream = $this->renderBlockView('@XutimCore/admin/block/block_item_form.html.twig', 'stream_success', [
                    'block' => $block,
                    'item' => $blockItem
                ]);

                $this->addFlash('stream', $stream);
            }

            return $this->redirectToRoute('admin_block_show', ['id' => $block->getId()], Response::HTTP_SEE_OTHER);
        }

        return $this->render('@XutimCore/admin/block/block_item_form.html.twig', [
            'form' => $form,
            'block' => $block
        ]);
    }
}
