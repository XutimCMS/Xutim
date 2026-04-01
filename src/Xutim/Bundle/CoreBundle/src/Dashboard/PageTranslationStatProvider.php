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

        return new TranslationStat(
            label: 'pages',
            icon: 'tabler:file-text',
            untranslatedCount: $this->pageRepository->countUntranslatedForLocales($localesWithoutReference),
            outdatedCount: 0,
            listUrl: $this->router->generate('admin_page_list'),
        );
    }
}
