<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Action\Admin\Media;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Xutim\CoreBundle\Domain\Model\FileTranslationInterface;
use Xutim\CoreBundle\Repository\FileRepository;

#[Route('/json/file/all-list', name: 'admin_json_file_all_list', methods: ['get'])]
class JsonListAllFilesAction extends AbstractController
{
    public function __construct(
        private readonly FileRepository $fileRepo
    ) {
    }

    public function __invoke(): JsonResponse
    {
        $files = $this->fileRepo->findAllNonImages();

        $titles = [];
        foreach ($files as $file) {
            /** @var FileTranslationInterface $trans */
            $trans = $file->getTranslations()->first();
            $titles[$file->getId()->toRfc4122()] = $trans->getName();
        }
        
        return $this->json($titles);
    }
}
