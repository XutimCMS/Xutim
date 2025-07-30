<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Action\Admin\Article;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Xutim\CoreBundle\Repository\ArticleRepository;

class JsonListArticlesAction extends AbstractController
{
    public function __construct(
        private readonly ArticleRepository $articleRepository
    ) {
    }

    public function __invoke(): JsonResponse
    {
        $articles = $this->articleRepository->findAll();

        $titles = [];
        foreach ($articles as $article) {
            $titles[$article->getId()->toRfc4122()] = $article->getDefaultTranslation()->getTitle();
        }
        
        return $this->json($titles);
    }
}
