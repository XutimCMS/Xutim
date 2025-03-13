<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Action\Admin\Media;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\RouterInterface;
use Xutim\CoreBundle\Repository\FileRepository;

class JsonListFilesAction extends AbstractController
{
    public function __construct(
        private readonly FileRepository $fileRepository,
        private readonly RouterInterface $router
    ) {
    }

    #[Route('/json/file/list', name: 'admin_json_file_list', methods: ['get'])]
    public function uploadArticle(Request $request): Response
    {
        $ids = $this->fileRepository->findAllReferences();
        $fileUrls = array_map(fn (array $id) => $this->router->generate('public_show_file', [
            'ref' => $id['reference'],
            'extension' => $id['extension']
        ]), $ids);

        return $this->json($fileUrls);
    }
}
