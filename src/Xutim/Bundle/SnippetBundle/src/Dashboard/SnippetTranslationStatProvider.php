<?php

declare(strict_types=1);

namespace Xutim\SnippetBundle\Dashboard;

use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;
use Xutim\CoreBundle\Dashboard\LocaleStat;
use Xutim\CoreBundle\Dashboard\TranslationStat;
use Xutim\CoreBundle\Dashboard\TranslationStatProvider;
use Xutim\CoreBundle\Dto\Admin\FilterDto;
use Xutim\CoreBundle\Routing\AdminUrlGenerator;
use Xutim\SnippetBundle\Repository\SnippetRepository;

#[AutoconfigureTag('xutim.translation_stat_provider', ['priority' => 20])]
final readonly class SnippetTranslationStatProvider implements TranslationStatProvider
{
    public function __construct(
        private SnippetRepository $snippetRepository,
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
            $filter = new FilterDto(cols: ['notTranslatedInLocales' => [$locale]]);
            $qb = $this->snippetRepository->queryByFilter($filter);
            $qb->select('COUNT(DISTINCT snippet.id)');
            $qb->resetDQLPart('orderBy');
            $count = (int) $qb->getQuery()->getSingleScalarResult();

            if ($count > 0) {
                $localeBreakdown[] = new LocaleStat(
                    locale: $locale,
                    count: $count,
                    url: $this->router->generate('admin_snippet_list', [
                        '_content_locale' => $locale,
                        'col' => [
                            'notTranslatedInLocales' => [$locale],
                        ],
                    ]),
                );
                $totalCount += $count;
            }
        }

        return new TranslationStat(
            label: 'snippets',
            icon: 'tabler:abc',
            untranslatedCount: $totalCount,
            outdatedCount: 0,
            listUrl: $this->router->generate('admin_snippet_list', [
                'col' => [
                    'notTranslatedInLocales' => $localesWithoutReference,
                ],
            ]),
            localeBreakdown: $localeBreakdown,
        );
    }
}
