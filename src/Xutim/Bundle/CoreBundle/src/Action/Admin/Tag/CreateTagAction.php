<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Action\Admin\Tag;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Attribute\Route;
use Xutim\CoreBundle\Form\Admin\Dto\TagDto;
use Xutim\CoreBundle\Form\Admin\TagType;
use Xutim\CoreBundle\Message\Command\Tag\CreateTagCommand;
use Xutim\SecurityBundle\Security\UserRoles;
use Xutim\SecurityBundle\Service\UserStorage;

#[Route('/tag/new', name: 'admin_tag_new', methods: ['get', 'post'])]
class CreateTagAction extends AbstractController
{
    public function __construct(
        private readonly MessageBusInterface $commandBus,
        private readonly UserStorage $userStorage,
    ) {
    }

    public function __invoke(Request $request): Response
    {
        $this->denyAccessUnlessGranted(UserRoles::ROLE_EDITOR);
        $form = $this->createForm(TagType::class);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            /** @var TagDto $tagDto */
            $tagDto = $form->getData();

            $this->commandBus->dispatch(CreateTagCommand::fromDto(
                $tagDto,
                $this->userStorage->getUserWithException()->getUserIdentifier()
            ));

            $this->addFlash('success', 'flash.changes_made_successfully');

            return $this->redirectToRoute('admin_tag_list', ['searchTerm' => '']);
        }

        return $this->render('@XutimCore/admin/tag/tag_new.html.twig', [
            'form' => $form
        ]);
    }
}
