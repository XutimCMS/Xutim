<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Action\Admin\Article;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Xutim\CoreBundle\Repository\ArticleRepository;

#[Route('/article/{id}/show-translation/{locale}', name: 'admin_article_translation_show')]
class ShowArticleTranslationAction extends AbstractController
{
    public function __construct(private readonly ArticleRepository $articleRepo)
    {
    }

    public function __invoke(string $id, string $locale): Response
    {
        $article = $this->articleRepo->find($id);
        if ($article === null) {
            throw $this->createNotFoundException('The article does not exist');
        }
        return $this->render('@XutimCore/admin/article_translation/show.html.twig', [
            'article' => $article,
            'translation' => $article->getTranslationByLocale($locale)
        ]);
    }
}
