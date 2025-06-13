<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Action\Admin\Block;

use App\Entity\Core\Block;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Xutim\CoreBundle\Context\BlockContext;
use Xutim\CoreBundle\Domain\Event\Block\BlockDeletedEvent;
use Xutim\CoreBundle\Domain\Factory\LogEventFactory;
use Xutim\CoreBundle\Entity\User;
use Xutim\CoreBundle\Form\Admin\DeleteType;
use Xutim\CoreBundle\Repository\BlockItemRepository;
use Xutim\CoreBundle\Repository\BlockRepository;
use Xutim\CoreBundle\Repository\LogEventRepository;
use Xutim\CoreBundle\Security\UserStorage;

#[Route('/block/delete/{id}', name: 'admin_block_delete', methods: ['post', 'get'])]
class DeleteBlockAction extends AbstractController
{
    public function __construct(
        private readonly LogEventFactory $logEventFactory,
        private readonly BlockRepository $blockRepo,
        private readonly BlockItemRepository $blockItemRepo,
        private readonly UserStorage $userStorage,
        private readonly LogEventRepository $eventRepo,
        private readonly BlockContext $blockContext
    ) {
    }

    public function __invoke(Request $request, string $id): Response
    {
        $block = $this->blockRepo->find($id);
        if ($block === null) {
            throw $this->createNotFoundException('The block does not exist');
        }
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
            $logEntry = $this->logEventFactory->create($id, $userIdentifier, Block::class, $event);

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
