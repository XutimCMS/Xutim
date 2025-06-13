<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Action\Admin\PublicationStatus;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Requirement\EnumRequirement;
use Symfony\UX\Turbo\TurboBundle;
use Xutim\CoreBundle\Entity\PublicationStatus;
use Xutim\CoreBundle\Entity\User;
use Xutim\CoreBundle\Message\Command\PublicationStatus\ChangeTagPublicationStatusCommand;
use Xutim\CoreBundle\Repository\TagRepository;
use Xutim\CoreBundle\Security\UserStorage;
use Xutim\CoreBundle\Service\CsrfTokenChecker;

#[Route(
    '/publication-status/tag/edit/{id}/{status}',
    name: 'admin_tag_publication_status_edit',
    requirements: ['status' => new EnumRequirement(PublicationStatus::class)],
    methods: ['post']
)]
class ChangeTagStatusAction extends AbstractController
{
    public function __construct(
        private readonly MessageBusInterface $commandBus,
        private readonly UserStorage $userStorage,
        private readonly CsrfTokenChecker $csrfTokenChecker,
        private readonly TagRepository $tagRepo
    ) {
    }

    public function __invoke(
        Request $request,
        string $id,
        PublicationStatus $status
    ): Response {
        $tag = $this->tagRepo->find($id);
        if ($tag === null) {
            throw $this->createNotFoundException('The tag does not exist');
        }
        $this->denyAccessUnlessGranted(User::ROLE_EDITOR);
        $this->csrfTokenChecker->checkTokenFromFormRequest('pulse-dialog', $request);

        $user = $this->userStorage->getUserWithException();
        $command = new ChangeTagPublicationStatusCommand(
            $tag->getId(),
            $status,
            $user->getUserIdentifier()
        );
        $this->commandBus->dispatch($command);
        $this->addFlash('success', 'flash.changes_made_successfully');

        if (TurboBundle::STREAM_FORMAT === $request->getPreferredFormat()) {
            $request->setRequestFormat(TurboBundle::STREAM_FORMAT);

            $stream = $this->renderBlockView('@XutimCore/admin/tag/_status_item.html.twig', 'stream_success', [
                'translation' => $tag
            ]);
            $this->addFlash('stream', $stream);

            return $this->redirect($request->headers->get('referer', '/'));
        }

        return $this->redirect($request->headers->get('referer', '/'));
    }
}
