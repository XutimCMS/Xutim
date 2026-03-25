<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Action\Admin;

use Pagerfanta\Adapter\ArrayAdapter;
use Pagerfanta\Pagerfanta;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapQueryParameter;
use Xutim\CoreBundle\Context\SiteContext;
use Xutim\CoreBundle\Domain\Model\ArticleInterface;
use Xutim\CoreBundle\Repository\ArticleRepository;
use Xutim\NotificationBundle\Repository\NotificationRepository;
use Xutim\SecurityBundle\Domain\Model\UserInterface;

/**
 * @method UserInterface getUser()
 */
class AdminHomepageAction extends AbstractController
{
    public function __construct(
        private readonly ArticleRepository $articleRepository,
        private readonly SiteContext $siteContext,
        private readonly NotificationRepository $notificationRepository,
    ) {
    }

    public function __invoke(
        #[MapQueryParameter] int $untranslatedPage = 1,
        #[MapQueryParameter] int $changedPage = 1,
    ): Response {
        $latestArticles = $this->articleRepository->findBy([], ['createdAt' => 'desc'], 10);
        $articlesCount = $this->articleRepository->getArticlesCount();

        if ($this->isGranted('ROLE_TRANSLATOR')) {
            return $this->renderDashboardForTranslators($latestArticles, $articlesCount, $untranslatedPage, $changedPage);
        }

        return $this->renderDashboardForUsers($latestArticles);
    }

    /**
     * @param array<ArticleInterface> $latestArticles
     */
    private function renderDashboardForTranslators(
        array $latestArticles,
        int $articlesCount,
        int $untranslatedPage,
        int $changedPage,
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

        $untranslatedPager = Pagerfanta::createForCurrentPageWithMaxPerPage(
            new ArrayAdapter($this->articleRepository->findByMissingTranslations($userLocales)),
            $untranslatedPage,
            10
        );
        $changedPager = Pagerfanta::createForCurrentPageWithMaxPerPage(
            new ArrayAdapter($this->articleRepository->findByChangedDefaultTranslations($userLocales)),
            $changedPage,
            10
        );

        $latestNotifications = $this->notificationRepository->findLatestForRecipient($this->getUser(), 5);

        return $this->render('@XutimCore/admin/homepage/homepage_translator.html.twig', [
            'latestArticles' => $latestArticles,
            'untranslatedPager' => $untranslatedPager,
            'changedPager' => $changedPager,
            'latestNotifications' => $latestNotifications,
            'articlesCount' => $articlesCount,
            'translatedLocalesCount' => $translatedLocalesCount,
            'userLocales' => $this->getUser()->getTranslationLocales(),
            'referenceLocale' => $this->siteContext->getReferenceLocale(),
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
