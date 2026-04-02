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

        return new TranslationStat(
            label: 'tags',
            icon: 'tabler:tag',
            untranslatedCount: $this->tagRepository->countUntranslatedForLocales($localesWithoutReference),
            outdatedCount: 0,
            listUrl: $this->router->generate('admin_tag_list', ['col' => ['translationStatus' => 'missing', 'publicationStatus' => 'published']]),
        );
    }
}
