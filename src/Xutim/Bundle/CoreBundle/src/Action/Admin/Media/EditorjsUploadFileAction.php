<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Action\Admin\Media;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Xutim\CoreBundle\Entity\Article;
use Xutim\CoreBundle\Entity\Page;
use Xutim\CoreBundle\Entity\User;
use Xutim\CoreBundle\Message\Command\File\UploadFileMessage;
use Xutim\CoreBundle\Repository\FileRepository;
use Xutim\CoreBundle\Security\UserStorage;

class EditorjsUploadFileAction extends AbstractController
{
    public function __construct(
        private readonly UserStorage $userStorage,
        private readonly MessageBusInterface $commandBus,
        private readonly FileRepository $fileRepository
    ) {
    }

    #[Route('/editorjs/file/upload/article/{id}', name: 'admin_editorjs_file_upload_article', methods: ['post'])]
    public function uploadArticle(Request $request, Article $article): Response
    {
        return $this->uploadFile($request, null, $article);
    }

    #[Route('/editorjs/file/upload/page/{id}', name: 'admin_editorjs_file_upload_page', methods: ['post'])]
    public function uploadPage(Request $request, Page $page): Response
    {
        return $this->uploadFile($request, $page, null);
    }

    #[Route('/editorjs/file/upload', name: 'admin_editorjs_file_upload', methods: ['post'])]
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
        ], UrlGeneratorInterface::ABSOLUTE_PATH);

        return $this->json(['success' => 1, 'file' => ['url' => $fileUrl]]);
    }
}
