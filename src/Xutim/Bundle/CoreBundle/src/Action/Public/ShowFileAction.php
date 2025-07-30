<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Action\Public;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Xutim\CoreBundle\Repository\FileRepository;
use Xutim\CoreBundle\Service\FileUploader;

class ShowFileAction extends AbstractController
{
    public function __construct(
        private readonly FileUploader $fileUploader,
        private readonly FileRepository $fileRepo
    ) {
    }

    public function __invoke(string $id): Response
    {
        $file = $this->fileRepo->find($id);
        if ($file === null) {
            throw $this->createNotFoundException('The file does not exist');
        }
        $path = sprintf('%s%s', $this->fileUploader->getFilesPath(), $file->getFileName());

        $response = $this->file($path, $file->getFileName());

        $response->setPublic();
        $response->setMaxAge(604800); // 1 week
        $response->setSharedMaxAge(604800);
        $response->setExpires((new \DateTime())->modify('+1 week'));
        $response->headers->addCacheControlDirective('must-revalidate', true);

        return $response;
    }
}
