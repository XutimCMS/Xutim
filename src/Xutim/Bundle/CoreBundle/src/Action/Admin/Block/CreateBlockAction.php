<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Action\Admin\Block;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBusInterface;
use Xutim\CoreBundle\Domain\Event\Block\BlockCreatedEvent;
use Xutim\CoreBundle\Domain\Factory\BlockFactory;
use Xutim\CoreBundle\Form\Admin\BlockType;
use Xutim\CoreBundle\Form\Admin\Dto\BlockDto;
use Xutim\CoreBundle\Message\Event\DomainEventMessage;
use Xutim\CoreBundle\Repository\BlockRepository;
use Xutim\CoreBundle\Routing\AdminUrlGenerator;
use Xutim\SecurityBundle\Security\UserRoles;
use Xutim\SecurityBundle\Service\UserStorage;

class CreateBlockAction extends AbstractController
{
    public function __construct(
        private readonly BlockRepository $blockRepository,
        private readonly UserStorage $userStorage,
        private readonly MessageBusInterface $eventBus,
        private readonly BlockFactory $blockFactory,
        private readonly AdminUrlGenerator $router,
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

            return new RedirectResponse($this->router->generate('admin_block_list', ['searchTerm' => '']));
        }

        return $this->render('@XutimCore/admin/block/block_form.html.twig', [
            'form' => $form
        ]);
    }
}
