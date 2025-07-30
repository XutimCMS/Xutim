<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Action\Admin\Media;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Xutim\CoreBundle\Domain\Model\FileTranslationInterface;
use Xutim\CoreBundle\Repository\FileRepository;

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
