<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Action\Public;

use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Xutim\CoreBundle\Entity\File;
use Xutim\CoreBundle\Entity\User;
use Xutim\CoreBundle\Service\FileUploader;

#[Route('/file/{ref}.{extension}', name: 'public_show_file')]
class ShowFileActionController extends AbstractController
{
    public function __invoke(
        FileUploader $fileUploader,
        #[MapEntity(mapping: ['ref' => 'reference'])]
        File $file
    ): Response {
        if ($this->isGranted(User::ROLE_ADMIN) === false) {
            // TODO: Check if article/page/block of the image is published.
        }
        $path = sprintf('%s%s', $fileUploader->getFilesPath(), $file->getFileName());

        return $this->file($path, $file->getFileName());
    }
}
