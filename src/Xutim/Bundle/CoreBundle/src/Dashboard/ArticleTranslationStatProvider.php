<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Dashboard;

use Xutim\CoreBundle\Context\SiteContext;
use Xutim\CoreBundle\Repository\ArticleRepository;
use Xutim\CoreBundle\Routing\AdminUrlGenerator;

final readonly class ArticleTranslationStatProvider implements TranslationStatProvider
{
    public function __construct(
        private ArticleRepository $articleRepository,
        private AdminUrlGenerator $router,
        private SiteContext $siteContext,
    ) {
    }

    public function getStat(array $locales, string $referenceLocale): TranslationStat
    {
        $ageLimitDays = $this->siteContext->getUntranslatedArticleAgeLimitDays();

        return new TranslationStat(
            label: 'articles',
            icon: 'tabler:article',
            untranslatedCount: count($this->articleRepository->findByMissingTranslations($locales, ageLimitDays: $ageLimitDays)),
            outdatedCount: count($this->articleRepository->findByChangedDefaultTranslations($locales)),
            listUrl: $this->router->generate('admin_article_list', ['translationStatus' => 'missing']),
        );
    }
}
