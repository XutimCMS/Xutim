<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Action\Admin\Article;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Xutim\CoreBundle\Repository\ArticleRepository;
use Xutim\CoreBundle\Repository\PageRepository;

class ShowArticleImagesStreamAction extends AbstractController
{
    public function __construct(
        private readonly ArticleRepository $articleRepo,
        private readonly PageRepository $pageRepo,
    ) {
    }
    #[Route('/article/images-stream/{id}', name: 'admin_article_images_stream', methods: ['get'])]
    public function showArticles(string $id): Response
    {
        $article = $this->articleRepo->find($id);
        if ($article === null) {
            throw $this->createNotFoundException('The article does not exist');
        }

        $this->denyAccessUnlessGranted('list', 'media');
        return $this->render('@XutimCore/admin/translation/_file_container_stream.html.twig', [
            'object' => $article
        ]);
    }

    #[Route('/page/images-stream/{id}', name: 'admin_page_images_stream', methods: ['get'])]
    public function showPages(string $id): Response
    {
        $page = $this->pageRepo->find($id);
        if ($page === null) {
            throw $this->createNotFoundException('The page does not exist');
        }
        $this->denyAccessUnlessGranted('list', 'media');
        return $this->render('@XutimCore/admin/translation/_file_container_stream.html.twig', [
            'object' => $page
        ]);
    }
}
