<?php

declare(strict_types=1);

namespace Xutim\SnippetBundle\Dashboard;

use Xutim\CoreBundle\Dashboard\TranslationStat;
use Xutim\CoreBundle\Dashboard\TranslationStatProvider;
use Xutim\CoreBundle\Dto\Admin\FilterDto;
use Xutim\CoreBundle\Routing\AdminUrlGenerator;
use Xutim\SnippetBundle\Repository\SnippetRepository;

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

        $count = 0;
        if ($localesWithoutReference !== []) {
            $filter = FilterDto::fromArray(['notTranslatedInLocales' => $localesWithoutReference]);
            $qb = $this->snippetRepository->queryByFilter($filter);
            $qb->select('COUNT(DISTINCT snippet.id)');
            $qb->resetDQLPart('orderBy');
            $count = (int) $qb->getQuery()->getSingleScalarResult();
        }

        return new TranslationStat(
            label: 'snippets',
            icon: 'tabler:code',
            untranslatedCount: $count,
            outdatedCount: 0,
            listUrl: $this->router->generate('admin_snippet_list'),
        );
    }
}
