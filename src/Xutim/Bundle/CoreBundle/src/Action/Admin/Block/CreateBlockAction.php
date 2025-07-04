<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Action\Admin\Block;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Attribute\Route;
use Xutim\CoreBundle\Domain\Event\Block\BlockCreatedEvent;
use Xutim\CoreBundle\Domain\Factory\BlockFactory;
use Xutim\CoreBundle\Form\Admin\BlockType;
use Xutim\CoreBundle\Form\Admin\Dto\BlockDto;
use Xutim\CoreBundle\Message\Event\DomainEventMessage;
use Xutim\CoreBundle\Repository\BlockRepository;
use Xutim\SecurityBundle\Security\UserRoles;
use Xutim\SecurityBundle\Service\UserStorage;

#[Route('/block/new', name: 'admin_block_new', methods: ['get', 'post'])]
class CreateBlockAction extends AbstractController
{
    public function __construct(
        private readonly BlockRepository $blockRepository,
        private readonly UserStorage $userStorage,
        private readonly MessageBusInterface $eventBus,
        private readonly BlockFactory $blockFactory
    ) {
    }

    public function __invoke(Request $request): Response
    {
        $this->denyAccessUnlessGranted(UserRoles::ROLE_DEVELOPER);
        $form = $this->createForm(BlockType::class);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            /** @var BlockDto $dto */
            $dto = $form->getData();

            $block = $this->blockFactory->create($dto->code, $dto->name, $dto->description, $dto->colorHex, $dto->layout);
            $this->blockRepository->save($block, true);

            $this->eventBus->dispatch(new DomainEventMessage(
                $block->getId(),
                $block::class,
                BlockCreatedEvent::fromBlock($block),
                $this->userStorage->getUserWithException()->getUserIdentifier()
            ));

            return $this->redirectToRoute('admin_block_list', ['searchTerm' => '']);
        }

        return $this->render('@XutimCore/admin/block/block_form.html.twig', [
            'form' => $form
        ]);
    }
}
