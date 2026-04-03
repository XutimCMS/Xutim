<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Dashboard;

use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;
use Xutim\CoreBundle\Repository\PageRepository;
use Xutim\CoreBundle\Routing\AdminUrlGenerator;

#[AutoconfigureTag('xutim.translation_stat_provider', ['priority' => 50])]
final readonly class PageTranslationStatProvider implements TranslationStatProvider
{
    public function __construct(
        private PageRepository $pageRepository,
        private AdminUrlGenerator $router,
    ) {
    }

    public function getStat(array $locales, string $referenceLocale): TranslationStat
    {
        $localesWithoutReference = array_values(array_filter(
            $locales,
            static fn (string $l) => $l !== $referenceLocale,
        ));

        $totalCount = 0;
        $localeBreakdown = [];

        foreach ($localesWithoutReference as $locale) {
            $count = $this->pageRepository->countUntranslatedForLocales([$locale]);
            if ($count > 0) {
                $localeBreakdown[] = new LocaleStat(
                    locale: $locale,
                    count: $count,
                    url: $this->router->generate('admin_page_translation_list', [
                        '_content_locale' => $locale,
                        'col' => ['translationStatus' => 'missing'],
                    ]),
                );
                $totalCount += $count;
            }
        }

        $unpublishedCount = $this->pageRepository->countUnpublishedForLocales($localesWithoutReference);

        return new TranslationStat(
            label: 'pages',
            icon: 'tabler:folder',
            untranslatedCount: $totalCount,
            outdatedCount: 0,
            listUrl: $this->router->generate('admin_page_translation_list', ['col' => ['translationStatus' => 'missing']]),
            unpublishedCount: $unpublishedCount,
            localeBreakdown: $localeBreakdown,
            untranslatedUrl: $this->router->generate('admin_page_translation_list', ['col' => ['translationStatus' => 'missing']]),
            unpublishedUrl: $unpublishedCount > 0 ? $this->router->generate('admin_page_translation_list', ['col' => ['publicationStatus' => 'draft']]) : null,
        );
    }
}
