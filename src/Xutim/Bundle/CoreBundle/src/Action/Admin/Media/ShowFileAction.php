<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Action\Admin\Media;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Xutim\CoreBundle\Repository\FileTranslationRepository;

class ShowFileAction extends AbstractController
{
    public function __invoke(string $id, FileTranslationRepository $transRepo): Response
    {
        $translation = $transRepo->find($id);
        if ($translation === null) {
            throw $this->createNotFoundException('The file translation does not exist');
        }
        return $this->render('@XutimCore/admin/media/show_translation.html.twig', [
            'file' => $translation->getFile(),
            'translation' => $translation,
        ]);
    }
}
