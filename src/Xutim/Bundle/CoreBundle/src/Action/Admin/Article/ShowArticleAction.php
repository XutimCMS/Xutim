<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Action\Admin\Article;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Xutim\CoreBundle\Context\Admin\ContentContext;
use Xutim\CoreBundle\Context\SiteContext;
use Xutim\CoreBundle\Entity\Article;
use Xutim\CoreBundle\Entity\User;
use Xutim\CoreBundle\Repository\ArticleRepository;
use Xutim\CoreBundle\Repository\EventRepository;

#[Route('/article/{id<[^/]+>}', name: 'admin_article_show', methods: ['get'])]
class ShowArticleAction extends AbstractController
{
    public function __construct(
        private readonly SiteContext $siteContext,
        private readonly ContentContext $contentContext,
        private readonly ArticleRepository $articleRepo,
        private readonly EventRepository $eventRepo
    ) {
    }

    public function __invoke(Article $article): Response
    {
        if ($this->isGranted('ROLE_ADMIN') === false && $this->isGranted('ROLE_TRANSLATOR')) {
            /** @var User $user */
            $user = $this->getUser();
            $locales = $user->getTranslationLocales();
            $totalTranslations = count($locales);
        } else {
            $locales = null;
            $totalTranslations = count($this->siteContext->getLocales());
        }

        $translatedArticles = $this->articleRepo->countTranslatedTranslations($article, $locales);

        $locale = $this->contentContext->getLanguage();
        $contextTranslation = $article->getTranslationByLocaleOrDefault($locale);

        $currentTrans = $article->getTranslationByLocale($locale);
        if ($currentTrans === null) {
            $revisionsCount = 0;
            $lastRevision = null;
        } else {
            $revisionsCount = $this->eventRepo->eventsCountPerTranslation($currentTrans);
            $lastRevision = $this->eventRepo->findLastByTranslation($currentTrans);
        }

        return $this->render('@XutimCore/admin/article/article_show.html.twig', [
            'article' => $article,
            'currentTranslation' => $currentTrans,
            'revisionsCount' => $revisionsCount,
            'lastRevision' => $lastRevision,
            'contextTranslation' => $contextTranslation,
            'totalTranslations' => $totalTranslations,
            'translatedTranslations' => $translatedArticles
        ]);
    }
}
