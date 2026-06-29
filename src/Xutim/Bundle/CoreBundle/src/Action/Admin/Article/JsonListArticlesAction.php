<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Action\Admin\Article;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Xutim\CoreBundle\Domain\Model\ContentTranslationInterface;
use Xutim\CoreBundle\Repository\ArticleRepository;
use Xutim\CoreBundle\Service\ReferenceTranslationResolver;

class JsonListArticlesAction extends AbstractController
{
    public function __construct(
        private readonly ArticleRepository $articleRepository,
        private readonly ReferenceTranslationResolver $referenceTranslationResolver,
    ) {
    }

    public function __invoke(): JsonResponse
    {
        $articles = $this->articleRepository->findAll();

        $titles = [];
        foreach ($articles as $article) {
            /** @var ContentTranslationInterface $reference */
            $reference = $this->referenceTranslationResolver->resolve($article);
            $titles[$article->getId()->toRfc4122()] = $reference->getTitle();
        }

        return $this->json($titles);
    }
}
