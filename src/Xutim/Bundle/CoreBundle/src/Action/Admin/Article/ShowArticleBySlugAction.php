<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Action\Admin\Article;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Xutim\CoreBundle\Repository\ContentTranslationRepository;

#[Route('/article/by-slug/{slug}', name: 'admin_article_show_by_slug')]
class ShowArticleBySlugAction extends AbstractController
{
    public function __invoke(Request $request, string $slug, ContentTranslationRepository $repo): Response
    {
        $trans = $repo->findOneBy(['slug' => $slug]);
        if ($trans === null) {
            throw $this->createNotFoundException(sprintf('The article translation with a slug %s was not found.', $slug));
        }

        return $this->forward(
            ShowArticleAction::class,
            ['id' => $trans->getArticle()->getId()]
        );
    }
}
