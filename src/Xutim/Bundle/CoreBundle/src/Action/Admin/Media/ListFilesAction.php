<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Action\Admin\Media;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Xutim\CoreBundle\Repository\FileRepository;

#[Route('/media', name: 'admin_media_list')]
class ListFilesAction extends AbstractController
{
    public function __construct(private readonly FileRepository $fileRepository)
    {
    }

    public function __invoke(Request $request): Response
    {
        $searchTerm = $request->query->getString('searchTerm');
        $files = $this->fileRepository->findBySearchTerm($searchTerm);

        return $this->render('@XutimCore/admin/media/list.html.twig', [
            'files' => $files
        ]);
    }
}
