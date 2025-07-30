<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Action\Admin\Article;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Xutim\CoreBundle\Repository\ContentTranslationRepository;

class ShowArticleBySlugAction extends AbstractController
{
    public function __invoke(string $slug, ContentTranslationRepository $repo): Response
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
