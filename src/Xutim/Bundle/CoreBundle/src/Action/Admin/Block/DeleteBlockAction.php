<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Action\Admin\Block;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Xutim\CoreBundle\Context\BlockContext;
use Xutim\CoreBundle\Domain\Event\Block\BlockDeletedEvent;
use Xutim\CoreBundle\Domain\Factory\LogEventFactory;
use Xutim\CoreBundle\Entity\Block;
use Xutim\CoreBundle\Form\Admin\DeleteType;
use Xutim\CoreBundle\Repository\BlockItemRepository;
use Xutim\CoreBundle\Repository\BlockRepository;
use Xutim\CoreBundle\Repository\LogEventRepository;
use Xutim\CoreBundle\Routing\AdminUrlGenerator;
use Xutim\SecurityBundle\Security\UserRoles;
use Xutim\SecurityBundle\Service\UserStorage;

class DeleteBlockAction extends AbstractController
{
    public function __construct(
        private readonly LogEventFactory $logEventFactory,
        private readonly BlockRepository $blockRepo,
        private readonly BlockItemRepository $blockItemRepo,
        private readonly UserStorage $userStorage,
        private readonly LogEventRepository $eventRepo,
        private readonly BlockContext $blockContext,
        private readonly AdminUrlGenerator $router,
    ) {
    }

    public function __invoke(Request $request, string $id): Response
    {
        $block = $this->blockRepo->find($id);
        if ($block === null) {
            throw $this->createNotFoundException('The block does not exist');
        }
        $this->denyAccessUnlessGranted(UserRoles::ROLE_DEVELOPER);
        $form = $this->createForm(DeleteType::class, [], [
            'action' => $this->router->generate('admin_block_delete', ['id' => $block->getId()]),
        ]);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            foreach ($block->getBlockItems() as $item) {
                $this->blockItemRepo->remove($item);
            }

            $id = $block->getId();
            $userIdentifier = $this->userStorage->getUserWithException()->getUserIdentifier();
            $event = new BlockDeletedEvent($id);
            $logEntry = $this->logEventFactory->create($id, $userIdentifier, Block::class, $event);

            $this->blockRepo->remove($block, true);
            $this->eventRepo->save($logEntry, true);
            $this->blockContext->resetAllLocalesBlockTemplate($block->getCode());

            return new RedirectResponse($this->router->generate('admin_block_list', ['searchTerm' => '']));
        }

        return $this->render('@XutimCore/admin/block/block_delete.html.twig', [
            'block' => $block,
            'form' => $form
        ]);
    }
}
