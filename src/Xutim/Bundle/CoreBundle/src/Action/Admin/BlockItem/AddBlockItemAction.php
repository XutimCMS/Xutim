<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Action\Admin\BlockItem;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\Translation\TranslatorInterface;
use Webmozart\Assert\Assert;
use Xutim\CoreBundle\Config\Layout\Block\BlockLayoutChecker;
use Xutim\CoreBundle\Context\BlockContext;
use Xutim\CoreBundle\Domain\Factory\BlockItemFactory;
use Xutim\CoreBundle\Form\Admin\BlockItemType;
use Xutim\CoreBundle\Repository\BlockItemRepository;
use Xutim\CoreBundle\Repository\BlockRepository;
use Xutim\CoreBundle\Routing\AdminUrlGenerator;
use Xutim\SecurityBundle\Security\UserRoles;

class AddBlockItemAction extends AbstractController
{
    public function __construct(
        private readonly BlockItemRepository $blockItemRepository,
        private readonly BlockRepository $blockRepo,
        private readonly TranslatorInterface $translator,
        private readonly BlockContext $blockContext,
        private readonly BlockItemFactory $blockItemFactory,
        private readonly BlockLayoutChecker $blockLayoutChecker,
        private readonly AdminUrlGenerator $router
    ) {
    }

    public function __invoke(Request $request, string $id): Response
    {
        $block = $this->blockRepo->find($id);
        if ($block === null) {
            throw $this->createNotFoundException('The block does not exist');
        }

        $this->denyAccessUnlessGranted(UserRoles::ROLE_EDITOR);

        $form = $this->createForm(BlockItemType::class, null, [
            'action' => $this->router->generate('admin_block_add_item', ['id' => $block->getId()]),
            'block_options' => $this->blockLayoutChecker->extractAllowedOptions($block)
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            Assert::notNull($data);

            $blockItem = $this->blockItemFactory->create(
                $block,
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
                $data->coordinates?->longitude,
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

            $url = $this->router->generate('admin_block_show', ['id' => $block->getId()]);

            return new RedirectResponse($url, Response::HTTP_SEE_OTHER);
        }

        return $this->render('@XutimCore/admin/block/block_item_form.html.twig', [
            'form' => $form,
            'block' => $block
        ]);
    }
}
