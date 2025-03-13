<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Action\Public;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Xutim\CoreBundle\Entity\File;
use Xutim\CoreBundle\Service\FileUploader;

class ShowImageAction extends AbstractController
{
    public function __construct(private readonly FileUploader $fileUploader)
    {
    }

    #[Route('/file/show/{id}.{extension}', name: 'file_show', methods: ['get'])]
    public function __invoke(File $file): Response
    {
        $path = sprintf('%s%s', $this->fileUploader->getFilesPath(), $file->getFileName());

        return $this->file($path, $file->getFileName());
    }
}
