<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Action\Admin\Block;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Attribute\Route;
use Xutim\CoreBundle\Context\BlockContext;
use Xutim\CoreBundle\Domain\Event\Block\BlockChangedEvent;
use Xutim\CoreBundle\Entity\User;
use Xutim\CoreBundle\Form\Admin\BlockType;
use Xutim\CoreBundle\Form\Admin\Dto\BlockDto;
use Xutim\CoreBundle\Message\Event\DomainEventMessage;
use Xutim\CoreBundle\Repository\BlockRepository;
use Xutim\CoreBundle\Security\UserStorage;

#[Route('/block/edit/{id}', name: 'admin_block_edit', methods: ['get', 'post'])]
final class EditBlockAction extends AbstractController
{
    public function __construct(
        private readonly BlockRepository $blockRepo,
        private readonly UserStorage $userStorage,
        private readonly MessageBusInterface $eventBus,
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
        $form = $this->createForm(BlockType::class, BlockDto::fromBlock($block));
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            /** @var BlockDto $dto */
            $dto = $form->getData();

            $block->change($dto->code, $dto->name, $dto->description, $dto->layout);
            $this->blockRepo->save($block, true);
            $this->blockContext->resetAllLocalesBlockTemplate($block->getCode());

            $this->eventBus->dispatch(new DomainEventMessage(
                $block->getId(),
                $block::class,
                BlockChangedEvent::fromBlock($block),
                $this->userStorage->getUserWithException()->getUserIdentifier()
            ));

            return $this->redirectToRoute('admin_block_list', ['searchTerm' => '']);
        }

        return $this->render('@XutimCore/admin/block/block_form.html.twig', [
            'form' => $form,
            'block' => $block
        ]);
    }
}
