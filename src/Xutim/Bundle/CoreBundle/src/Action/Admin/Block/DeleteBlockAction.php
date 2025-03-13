<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Action\Admin\Block;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Xutim\CoreBundle\Context\BlockContext;
use Xutim\CoreBundle\Domain\Event\Block\BlockDeletedEvent;
use Xutim\CoreBundle\Entity\Block;
use Xutim\CoreBundle\Entity\Event;
use Xutim\CoreBundle\Entity\User;
use Xutim\CoreBundle\Form\Admin\DeleteType;
use Xutim\CoreBundle\Repository\BlockItemRepository;
use Xutim\CoreBundle\Repository\BlockRepository;
use Xutim\CoreBundle\Repository\EventRepository;
use Xutim\CoreBundle\Security\UserStorage;

#[Route('/block/delete/{id}', name: 'admin_block_delete', methods: ['post', 'get'])]
class DeleteBlockAction extends AbstractController
{
    public function __construct(
        private readonly BlockRepository $blockRepo,
        private readonly BlockItemRepository $blockItemRepo,
        private readonly UserStorage $userStorage,
        private readonly EventRepository $eventRepo,
        private readonly BlockContext $blockContext
    ) {
    }

    public function __invoke(Request $request, Block $block): Response
    {
        $this->denyAccessUnlessGranted(User::ROLE_DEVELOPER);
        $form = $this->createForm(DeleteType::class, [], [
            'action' => $this->generateUrl('admin_block_delete', ['id' => $block->getId()]),
        ]);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            foreach ($block->getBlockItems() as $item) {
                $this->blockItemRepo->remove($item);
            }

            $id = $block->getId();
            $userIdentifier = $this->userStorage->getUserWithException()->getUserIdentifier();
            $event = new BlockDeletedEvent($id);
            $logEntry = new Event($id, $userIdentifier, Block::class, $event);

            $this->blockRepo->remove($block, true);
            $this->eventRepo->save($logEntry, true);
            $this->blockContext->resetAllLocalesBlockTemplate($block->getCode());

            return $this->redirectToRoute('admin_block_list', ['searchTerm' => '']);
        }

        return $this->render('@XutimCore/admin/block/block_delete.html.twig', [
            'block' => $block,
            'form' => $form
        ]);
    }
}
