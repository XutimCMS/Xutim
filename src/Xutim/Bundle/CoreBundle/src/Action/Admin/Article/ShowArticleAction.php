<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Action\Admin\Article;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Xutim\CoreBundle\Context\Admin\ContentContext;
use Xutim\CoreBundle\Context\SiteContext;
use Xutim\CoreBundle\Repository\ArticleRepository;
use Xutim\CoreBundle\Repository\LogEventRepository;
use Xutim\CoreBundle\Repository\TagRepository;
use Xutim\SecurityBundle\Security\UserInterface;

#[Route('/article/{id<[^/]+>}', name: 'admin_article_show', methods: ['get'])]
class ShowArticleAction extends AbstractController
{
    public function __construct(
        private readonly SiteContext $siteContext,
        private readonly ContentContext $contentContext,
        private readonly ArticleRepository $articleRepo,
        private readonly LogEventRepository $eventRepo,
        private readonly TagRepository $tagRepo
    ) {
    }

    public function __invoke(string $id): Response
    {
        $article = $this->articleRepo->find($id);
        if ($article === null) {
            throw $this->createNotFoundException('The article does not exist');
        }
        if ($this->isGranted('ROLE_ADMIN') === false && $this->isGranted('ROLE_TRANSLATOR')) {
            /** @var UserInterface $user */
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
            'translatedTranslations' => $translatedArticles,
            'allTags' => $this->tagRepo->findAll()
        ]);
    }
}
