<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Action\Admin;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Xutim\CoreBundle\Context\SiteContext;
use Xutim\CoreBundle\Domain\Model\ArticleInterface;
use Xutim\CoreBundle\Repository\ArticleRepository;
use Xutim\SecurityBundle\Domain\Model\UserInterface;

/**
 * @method UserInterface getUser()
 */
#[Route('/', name: 'admin_homepage')]
class AdminHomepageAction extends AbstractController
{
    public function __construct(
        private readonly ArticleRepository $articleRepository,
        private readonly SiteContext $siteContext
    ) {
    }

    public function __invoke(): Response
    {
        $latestArticles = $this->articleRepository->findBy([], ['createdAt' => 'desc'], 10);
        $articlesCount = $this->articleRepository->getArticlesCount();

        if ($this->isGranted('ROLE_TRANSLATOR')) {
            return $this->renderDashboardForTranslators($latestArticles, $articlesCount);
        }

        return $this->renderDashboardForUsers($latestArticles);
    }

    /**
     * @param array<ArticleInterface> $latestArticles
     */
    private function renderDashboardForTranslators(
        array $latestArticles,
        int $articlesCount
    ): Response {
        $userLocales = $this->getUser()->getTranslationLocales();
        $userLocales = array_filter(
            $userLocales,
            fn (string $locale) => in_array($locale, $this->siteContext->getLocales(), true)
        );

        $translatedLocalesCount = [];
        foreach ($userLocales as $locale) {
            $translatedLocalesCount[$locale] = $this->articleRepository->getTranslatedSumByLocale($locale);
        }
        $latestUntranslatedArticles = $this->articleRepository->findByMissingTranslations(
            $userLocales,
            10
        );
        $latestChangedArticles = $this->articleRepository->findByChangedDefaultTranslations(
            $userLocales,
            10
        );

        return $this->render('@XutimCore/admin/homepage/homepage_translator.html.twig', [
            'latestArticles' => $latestArticles,
            'latestUntranslatedArticles' => $latestUntranslatedArticles,
            'latestChangedArticles' => $latestChangedArticles,
            'articlesCount' => $articlesCount,
            'translatedLocalesCount' => $translatedLocalesCount,
            'userLocales' => $this->getUser()->getTranslationLocales()
        ]);
    }

    /**
     * @param array<ArticleInterface> $latestArticles
     */
    private function renderDashboardForUsers(array $latestArticles): Response
    {
        return $this->render('@XutimCore/admin/homepage/homepage.html.twig', [
            'latestArticles' => $latestArticles
        ]);
    }
}
