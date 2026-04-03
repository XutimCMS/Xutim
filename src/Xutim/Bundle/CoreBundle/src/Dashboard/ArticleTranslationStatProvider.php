<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Dashboard;

use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;
use Xutim\CoreBundle\Context\SiteContext;
use Xutim\CoreBundle\Repository\ArticleRepository;
use Xutim\CoreBundle\Routing\AdminUrlGenerator;

#[AutoconfigureTag('xutim.translation_stat_provider', ['priority' => 40])]
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

        $localesWithoutReference = array_values(array_filter(
            $locales,
            static fn (string $l) => $l !== $referenceLocale,
        ));

        $totalUntranslated = 0;
        $localeBreakdown = [];

        foreach ($localesWithoutReference as $locale) {
            $count = count($this->articleRepository->findByMissingTranslations([$locale, $referenceLocale], ageLimitDays: $ageLimitDays));
            if ($count > 0) {
                $localeBreakdown[] = new LocaleStat(
                    locale: $locale,
                    count: $count,
                    url: $this->router->generate('admin_article_list', [
                        '_content_locale' => $locale,
                        'col' => ['translationStatus' => 'missing', ...($ageLimitDays > 0 ? ['updatedAt' => (string) $ageLimitDays] : [])],
                    ]),
                );
                $totalUntranslated += $count;
            }
        }

        $ageLimitCol = $ageLimitDays > 0 ? ['updatedAt' => (string) $ageLimitDays] : [];

        return new TranslationStat(
            label: 'articles',
            icon: 'tabler:article',
            untranslatedCount: $totalUntranslated,
            outdatedCount: count($this->articleRepository->findByChangedDefaultTranslations($locales)),
            listUrl: $this->router->generate('admin_article_list', ['col' => ['translationStatus' => 'missing', ...$ageLimitCol]]),
            unpublishedCount: $this->articleRepository->countUnpublishedForLocales($localesWithoutReference),
            localeBreakdown: $localeBreakdown,
            untranslatedUrl: $this->router->generate('admin_article_list', ['col' => ['translationStatus' => 'missing', ...$ageLimitCol]]),
            outdatedUrl: $this->router->generate('admin_article_list', ['col' => ['translationStatus' => 'fallback', ...$ageLimitCol]]),
            unpublishedUrl: $this->router->generate('admin_article_list', ['col' => ['publicationStatus' => 'draft', 'translationStatus' => 'translated']]),
        );
    }
}
