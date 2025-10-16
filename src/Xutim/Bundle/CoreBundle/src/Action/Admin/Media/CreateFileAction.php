<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Action\Admin\Media;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Xutim\CoreBundle\Form\Admin\FileType;
use Xutim\CoreBundle\Message\Command\File\UploadFileMessage;
use Xutim\CoreBundle\Repository\FileRepository;
use Xutim\CoreBundle\Repository\MediaFolderRepository;
use Xutim\CoreBundle\Routing\AdminUrlGenerator;
use Xutim\CoreBundle\Service\FileService;
use Xutim\SecurityBundle\Security\UserRoles;
use Xutim\SecurityBundle\Service\UserStorage;

class CreateFileAction extends AbstractController
{
    public function __construct(
        private readonly UserStorage $userStorage,
        private readonly FileService $fileService,
        private readonly AdminUrlGenerator $router,
        private readonly MediaFolderRepository $folderRepository,
        private readonly FileRepository $fileRepository
    ) {
    }

    public function __invoke(Request $request, ?string $id): Response
    {
        $this->denyAccessUnlessGranted(UserRoles::ROLE_EDITOR);
        $folder = null;
        if ($id !== null) {
            $folder = $this->folderRepository->find($id);
            if ($folder === null) {
                throw $this->createNotFoundException('The media folder does not exist');
            }
        }

        $form = $this->createForm(FileType::class, null, [
            'action' => $this->router->generate('admin_media_new', ['id' => $id])
        ]);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            /** @var array{file: UploadedFile, name: string, alt: string|null, language: string, copyright: ?string} $data */
            $data = $form->getData();

            $command = new UploadFileMessage(
                $data['file'],
                $this->userStorage->getUserWithException()->getUserIdentifier(),
                null,
                null,
                $data['name'],
                $data['alt'] ?? '',
                $data['language'],
                $data['copyright'] ?? '',
            );
            $file = $this->fileService->persistFile($command);

            if ($folder !== null) {
                $file->changeFolder($folder);
                $this->fileRepository->save($file, true);
            }

            if ($request->headers->has('turbo-frame')) {
                $stream = $this->renderBlockView('@XutimCore/admin/media/new.html.twig', 'stream_success', [
                    'file' => $file
                ]);

                $this->addFlash('stream', $stream);
            }

            $redirectId = $folder?->getId();
            return new RedirectResponse(
                $this->router->generate('admin_media_list', ['id' => $redirectId]),
                Response::HTTP_SEE_OTHER
            );
        }

        return $this->render('@XutimCore/admin/media/new.html.twig', [
            'form' => $form
        ]);
    }
}
