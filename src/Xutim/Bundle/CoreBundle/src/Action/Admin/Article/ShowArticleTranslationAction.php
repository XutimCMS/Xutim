<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Action\Admin\Article;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Xutim\CoreBundle\Entity\Article;

#[Route('/article/{id}/show-translation/{locale}', name: 'admin_article_translation_show')]
class ShowArticleTranslationAction extends AbstractController
{
    public function __invoke(Article $article, string $locale): Response
    {
        return $this->render('@XutimCore/admin/article_translation/show.html.twig', [
            'article' => $article,
            'translation' => $article->getTranslationByLocale($locale)
        ]);
    }
}
