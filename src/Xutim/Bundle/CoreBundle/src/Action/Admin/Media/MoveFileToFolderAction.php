<?php

declare(strict_types=1);
namespace Xutim\CoreBundle\Action\Admin\Media;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Xutim\CoreBundle\Repository\FileRepository;
use Xutim\CoreBundle\Repository\MediaFolderRepository;

class MoveFileToFolderAction extends AbstractController
{
    public function __construct(
        private readonly FileRepository $fileRepo,
        private readonly MediaFolderRepository $folderRepo,
    ) {
    }

    public function __invoke(Request $request): Response
    {
        /** @var array{fileId: string, targetFolderId: string} $data */
        $data = json_decode($request->getContent(), true);
        $fileId = $data['fileId'];
        $folderId = $data['targetFolderId'];

        $file = $this->fileRepo->find($fileId);
        if ($file === null) {
            return new JsonResponse(['error' => 'File not found'], Response::HTTP_NOT_FOUND);
        }

        $folder = $this->folderRepo->find($folderId);
        if ($folder === null) {
            return new JsonResponse(['error' => 'Media folder not found'], Response::HTTP_NOT_FOUND);
        }

        $file->changeFolder($folder);
        $this->fileRepo->save($file, true);

        return new JsonResponse(['success' => true]);
    }
}
