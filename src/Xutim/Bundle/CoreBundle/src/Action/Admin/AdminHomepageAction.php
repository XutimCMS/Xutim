<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Action\Admin;

use Pagerfanta\Adapter\ArrayAdapter;
use Pagerfanta\Pagerfanta;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\Attribute\TaggedIterator;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapQueryParameter;
use Xutim\CoreBundle\Context\SiteContext;
use Xutim\CoreBundle\Dashboard\TranslationStatProvider;
use Xutim\CoreBundle\Domain\Model\ArticleInterface;
use Xutim\CoreBundle\Repository\ArticleRepository;
use Xutim\NotificationBundle\Repository\NotificationRepository;
use Xutim\SecurityBundle\Domain\Model\UserInterface;

/**
 * @method UserInterface getUser()
 */
class AdminHomepageAction extends AbstractController
{
    /**
     * @param iterable<TranslationStatProvider> $statProviders
     */
    public function __construct(
        private readonly ArticleRepository $articleRepository,
        private readonly SiteContext $siteContext,
        private readonly NotificationRepository $notificationRepository,
        #[TaggedIterator('xutim.translation_stat_provider')]
        private readonly iterable $statProviders,
    ) {
    }

    public function __invoke(
        #[MapQueryParameter] int $untranslatedPage = 1,
        #[MapQueryParameter] int $changedPage = 1,
    ): Response {
        $latestArticles = $this->articleRepository->findBy([], ['createdAt' => 'desc'], 10);

        if ($this->isGranted('ROLE_TRANSLATOR')) {
            return $this->renderDashboardForTranslators($latestArticles, $untranslatedPage, $changedPage);
        }

        return $this->renderDashboardForUsers($latestArticles);
    }

    /**
     * @param array<ArticleInterface> $latestArticles
     */
    private function renderDashboardForTranslators(
        array $latestArticles,
        int $untranslatedPage,
        int $changedPage,
    ): Response {
        $userLocales = $this->getUser()->getTranslationLocales();
        $userLocales = array_filter(
            $userLocales,
            fn (string $locale) => in_array($locale, $this->siteContext->getAllLocales(), true)
        );

        $referenceLocale = $this->siteContext->getReferenceLocale();

        $translationStats = [];
        foreach ($this->statProviders as $provider) {
            $stat = $provider->getStat($userLocales, $referenceLocale);
            if ($stat->hasWork()) {
                $translationStats[] = $stat;
            }
        }

        $ageLimitDays = $this->siteContext->getUntranslatedArticleAgeLimitDays();

        $untranslatedPager = Pagerfanta::createForCurrentPageWithMaxPerPage(
            new ArrayAdapter($this->articleRepository->findByMissingTranslations($userLocales, ageLimitDays: $ageLimitDays)),
            $untranslatedPage,
            10
        );
        $changedPager = Pagerfanta::createForCurrentPageWithMaxPerPage(
            new ArrayAdapter($this->articleRepository->findByChangedDefaultTranslations($userLocales)),
            $changedPage,
            10
        );

        $latestNotifications = $this->notificationRepository->findUnreadForRecipient($this->getUser(), 5);

        return $this->render('@XutimCore/admin/homepage/homepage_translator.html.twig', [
            'latestArticles' => $latestArticles,
            'untranslatedPager' => $untranslatedPager,
            'changedPager' => $changedPager,
            'latestNotifications' => $latestNotifications,
            'translationStats' => $translationStats,
            'userLocales' => $this->getUser()->getTranslationLocales(),
            'referenceLocale' => $referenceLocale,
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
