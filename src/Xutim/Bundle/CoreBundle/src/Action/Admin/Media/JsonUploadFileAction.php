<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Action\Admin\Media;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Attribute\Route;
use Xutim\CoreBundle\Entity\Article;
use Xutim\CoreBundle\Entity\Page;
use Xutim\CoreBundle\Entity\User;
use Xutim\CoreBundle\Message\Command\File\UploadFileMessage;
use Xutim\CoreBundle\Repository\FileRepository;
use Xutim\CoreBundle\Security\UserStorage;

class JsonUploadFileAction extends AbstractController
{
    public function __construct(
        private readonly UserStorage $userStorage,
        private readonly MessageBusInterface $commandBus,
        private readonly FileRepository $fileRepository
    ) {
    }

    #[Route('/json/file/upload/article/{id}', name: 'admin_json_file_upload_article', methods: ['post'])]
    public function uploadArticle(Request $request, Article $article): Response
    {
        return $this->uploadFile($request, null, $article);
    }

    #[Route('/json/file/upload/page/{id}', name: 'admin_json_file_upload_page', methods: ['post'])]
    public function uploadPage(Request $request, Page $page): Response
    {
        return $this->uploadFile($request, $page, null);
    }

    #[Route('/json/file/upload', name: 'admin_json_file_upload', methods: ['post'])]
    public function uploadDefault(Request $request): Response
    {
        return $this->uploadFile($request, null, null);
    }

    private function uploadFile(Request $request, ?Page $page, ?Article $article): Response
    {
        $this->denyAccessUnlessGranted(User::ROLE_EDITOR);
        /** @var UploadedFile $uploadedFile */
        $uploadedFile = $request->files->get('image');
        $message = new UploadFileMessage(
            $uploadedFile,
            $this->userStorage->getUserWithException()->getUserIdentifier(),
            $page,
            $article,
        );
        $this->commandBus->dispatch($message);

        $file = $this->fileRepository->find($message->id);
        if ($file === null) {
            throw new \Exception('File not was properly saved to the database.');
        }
        $fileUrl = $this->generateUrl('public_show_file', [
            'ref' => $file->getReference(),
            'extension' => $uploadedFile->getClientOriginalExtension()
        ]);

        return $this->json(['data' => ['filePath' => $fileUrl]]);
    }
}
