<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Action\Admin\Media;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Xutim\CoreBundle\Entity\User;
use Xutim\CoreBundle\Form\Admin\FileType;
use Xutim\CoreBundle\Message\Command\File\UploadFileMessage;
use Xutim\CoreBundle\Security\UserStorage;
use Xutim\CoreBundle\Service\FileService;

#[Route('/media/new', name: 'admin_media_new')]
class CreateFileAction extends AbstractController
{
    public function __construct(
        private readonly UserStorage $userStorage,
        private readonly FileService $fileService
    ) {
    }

    public function __invoke(Request $request): Response
    {
        $this->denyAccessUnlessGranted(User::ROLE_EDITOR);
        $form = $this->createForm(FileType::class, null, [
            'action' => $this->generateUrl('admin_media_new')
        ]);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            /** @var array{file: UploadedFile, name: string, alt: string|null, language: string} $data */
            $data = $form->getData();

            $command = new UploadFileMessage(
                $data['file'],
                $this->userStorage->getUserWithException()->getUserIdentifier(),
                null,
                null,
                $data['name'],
                $data['alt'] ?? '',
                $data['language'],
            );
            $file = $this->fileService->persistFile($command);

            if ($request->headers->has('turbo-frame')) {
                $stream = $this->renderBlockView('@XutimCore/admin/media/new.html.twig', 'stream_success', [
                    'file' => $file
                ]);

                $this->addFlash('stream', $stream);
            }

            return $this->redirectToRoute('admin_media_list', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('@XutimCore/admin/media/new.html.twig', [
            'form' => $form
        ]);
    }
}
