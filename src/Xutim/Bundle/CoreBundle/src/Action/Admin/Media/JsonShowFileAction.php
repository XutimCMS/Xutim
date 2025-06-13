<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Action\Admin\Media;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Xutim\CoreBundle\Repository\FileRepository;
use Xutim\CoreBundle\Service\FileUploader;

class JsonShowFileAction extends AbstractController
{
    public function __construct(
        private readonly FileUploader $fileUploader,
        private readonly string $publicUploadsDirectory,
        private readonly FileRepository $fileRepo
    ) {
    }

    #[Route('/json/file/show/{id}', name: 'admin_json_file_show', methods: ['get'])]
    public function __invoke(Request $request, string $id): Response
    {
        $file = $this->fileRepo->find($id);
        if ($file === null) {
            throw $this->createNotFoundException('The file does not exist');
        }
        $trans = $file->getTranslationByLocaleOrAny($request->getLocale());

        return $this->json([
            'id' => $file->getId(),
            'name' => $trans->getName(),
            'extension' => $file->getExtension(),
            'url' => sprintf('%s%s', $this->publicUploadsDirectory, $file->getFileName()),
            'size' => $this->fileUploader->getFileSize($file)
        ]);
    }
}
