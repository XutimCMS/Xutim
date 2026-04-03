<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Dashboard;

use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;
use Xutim\CoreBundle\Repository\TagRepository;
use Xutim\CoreBundle\Routing\AdminUrlGenerator;

#[AutoconfigureTag('xutim.translation_stat_provider', ['priority' => 30])]
final readonly class TagTranslationStatProvider implements TranslationStatProvider
{
    public function __construct(
        private TagRepository $tagRepository,
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
            $count = $this->tagRepository->countUntranslatedForLocales([$locale]);
            if ($count > 0) {
                $localeBreakdown[] = new LocaleStat(
                    locale: $locale,
                    count: $count,
                    url: $this->router->generate('admin_tag_list', [
                        '_content_locale' => $locale,
                        'col' => ['translationStatus' => 'missing', 'publicationStatus' => 'published'],
                    ]),
                );
                $totalCount += $count;
            }
        }

        return new TranslationStat(
            label: 'tags',
            icon: 'tabler:tag',
            untranslatedCount: $totalCount,
            outdatedCount: 0,
            listUrl: $this->router->generate('admin_tag_list', ['col' => ['translationStatus' => 'missing', 'publicationStatus' => 'published']]),
            localeBreakdown: $localeBreakdown,
        );
    }
}
