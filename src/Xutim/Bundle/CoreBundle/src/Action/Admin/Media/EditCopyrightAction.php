<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Action\Admin\Media;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\UX\Turbo\TurboBundle;
use Xutim\CoreBundle\Domain\Event\File\FileCopyrightUpdatedEvent;
use Xutim\CoreBundle\Domain\Factory\LogEventFactory;
use Xutim\CoreBundle\Entity\File;
use Xutim\CoreBundle\Form\Admin\FileCopyrightType;
use Xutim\CoreBundle\Repository\FileRepository;
use Xutim\CoreBundle\Repository\LogEventRepository;
use Xutim\SecurityBundle\Security\UserRoles;
use Xutim\SecurityBundle\Service\UserStorage;

#[Route('/media/copyright-edit/{id}', name: 'admin_media_copyright_edit')]
class EditCopyrightAction extends AbstractController
{
    public function __construct(
        private readonly LogEventFactory $logEventFactory,
        private readonly FileRepository $fileRepo,
        private readonly UserStorage $userStorage,
        private readonly LogEventRepository $eventRepository
    ) {
    }

    public function __invoke(Request $request, string $id): Response
    {
        $file = $this->fileRepo->find($id);
        if ($file === null) {
            throw $this->createNotFoundException('The file does not exist');
        }
        $this->denyAccessUnlessGranted(UserRoles::ROLE_EDITOR);
        $form = $this->createForm(FileCopyrightType::class, ['copyright' => $file->getCopyright()], [
            'action' => $this->generateUrl('admin_media_copyright_edit', ['id' => $file->getId()])
        ]);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            /** @var array{copyright: ?string} $data */
            $data = $form->getData();

            $file->changeCopyright($data['copyright'] ?? '');
            $this->fileRepo->save($file, true);

            $event = new FileCopyrightUpdatedEvent($file->getId(), $data['copyright'] ?? '');
            $logEntry = $this->logEventFactory->create(
                $file->getId(),
                $this->userStorage->getUserWithException()->getUserIdentifier(),
                File::class,
                $event
            );
            $this->eventRepository->save($logEntry, true);

            $this->addFlash('success', 'flash.changes_made_successfully');

            if (TurboBundle::STREAM_FORMAT === $request->getPreferredFormat()) {
                $request->setRequestFormat(TurboBundle::STREAM_FORMAT);
                $stream = $this->renderBlockView('@XutimCore/admin/media/edit_copyright.html.twig', 'stream_success', [
                    'file' => $file
                ]);
                $this->addFlash('stream', $stream);
            }

            $fallbackUrl = $this->generateUrl('admin_media_edit', [
                'id' => $file->getId()
            ]);

            return $this->redirect($request->headers->get('referer', $fallbackUrl));
        }

        return $this->render('@XutimCore/admin/media/edit_copyright.html.twig', [
            'form' => $form,
            'file' => $file
        ]);
    }
}
