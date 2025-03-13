<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Action\Admin\Media;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Xutim\CoreBundle\Entity\FileTranslation;

#[Route('/media/show-translation/{id}', name: 'admin_media_translation_show')]
class ShowFileAction extends AbstractController
{
    public function __invoke(FileTranslation $translation): Response
    {
        return $this->render('@XutimCore/admin/media/show_translation.html.twig', [
            'file' => $translation->getFile(),
            'translation' => $translation,
        ]);
    }
}
