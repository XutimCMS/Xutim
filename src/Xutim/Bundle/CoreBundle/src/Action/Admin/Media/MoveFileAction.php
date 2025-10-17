<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Action\Admin\Media;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Xutim\CoreBundle\Domain\Model\FileInterface;
use Xutim\CoreBundle\Repository\FileRepository;
use Xutim\CoreBundle\Repository\MediaFolderRepository;
use Xutim\CoreBundle\Routing\AdminUrlGenerator;
use Xutim\SecurityBundle\Security\UserRoles;

class MoveFileAction extends AbstractController
{
    public function __construct(
        private readonly AdminUrlGenerator $router,
        private readonly MediaFolderRepository $folderRepository,
        private readonly FileRepository $fileRepository
    ) {
    }

    public function __invoke(Request $request, FileInterface $file, ?string $folderId = null): Response
    {
        $this->denyAccessUnlessGranted(UserRoles::ROLE_EDITOR);

        if ($request->isMethod('POST')) {
            $folder = null;

            if ($folderId !== null && $folderId !== '') {
                $folder = $this->folderRepository->find($folderId);
                if ($folder === null) {
                    throw $this->createNotFoundException('The media folder does not exist');
                }
            }

            $currentFolder = $file->getMediaFolder();
            $file->changeFolder($folder);
            $this->fileRepository->save($file, true);

            if ($request->headers->has('turbo-frame')) {
                // Remove the file from the current view
                $removeStream = $this->renderBlockView('@XutimCore/admin/media/move.html.twig', 'stream_remove_file', [
                    'file' => $file
                ]);
                $this->addFlash('stream', $removeStream);

                // Update the source folder (where file was removed from)
                if ($currentFolder !== null) {
                    $refreshedCurrentFolder = $this->folderRepository->find($currentFolder->getId());
                    if ($refreshedCurrentFolder !== null) {
                        $updateSourceStream = $this->renderBlockView('@XutimCore/admin/media/move.html.twig', 'stream_update_folder', [
                            'folder' => $refreshedCurrentFolder
                        ]);
                        $this->addFlash('stream', $updateSourceStream);
                    }
                }

                // Update the destination folder (where file was moved to)
                if ($folder !== null) {
                    $refreshedDestFolder = $this->folderRepository->find($folder->getId());
                    if ($refreshedDestFolder !== null) {
                        $updateDestStream = $this->renderBlockView('@XutimCore/admin/media/move.html.twig', 'stream_update_folder', [
                            'folder' => $refreshedDestFolder
                        ]);
                        $this->addFlash('stream', $updateDestStream);
                    }
                }
            }

            $redirectId = $currentFolder?->getId();

            return new RedirectResponse(
                $this->router->generate('admin_media_list', ['id' => $redirectId]),
                Response::HTTP_SEE_OTHER
            );
        }

        $folders = $this->folderRepository->findAllOrderedHierarchically();

        return $this->render('@XutimCore/admin/media/move.html.twig', [
            'file' => $file,
            'folders' => $folders,
            'currentFolder' => $file->getMediaFolder()
        ]);
    }
}
