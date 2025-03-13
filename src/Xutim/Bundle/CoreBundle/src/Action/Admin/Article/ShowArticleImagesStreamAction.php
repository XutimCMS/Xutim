<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Action\Admin\Article;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Xutim\CoreBundle\Entity\Article;
use Xutim\CoreBundle\Entity\Page;

class ShowArticleImagesStreamAction extends AbstractController
{
    #[Route('/article/images-stream/{id}', name: 'admin_article_images_stream', methods: ['get'])]
    public function showArticles(Article $article): Response
    {
        $this->denyAccessUnlessGranted('list', 'media');
        return $this->render('@XutimCore/admin/translation/_file_container_stream.html.twig', [
            'object' => $article
        ]);
    }

    #[Route('/page/images-stream/{id}', name: 'admin_page_images_stream', methods: ['get'])]
    public function showPages(Page $page): Response
    {
        $this->denyAccessUnlessGranted('list', 'media');
        return $this->render('@XutimCore/admin/translation/_file_container_stream.html.twig', [
            'object' => $page
        ]);
    }
}
