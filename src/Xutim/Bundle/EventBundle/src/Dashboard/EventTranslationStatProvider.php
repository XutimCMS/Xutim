<?php

declare(strict_types=1);

namespace Xutim\EventBundle\Dashboard;

use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;
use Xutim\CoreBundle\Dashboard\LocaleStat;
use Xutim\CoreBundle\Dashboard\TranslationStat;
use Xutim\CoreBundle\Dashboard\TranslationStatProvider;
use Xutim\CoreBundle\Routing\AdminUrlGenerator;
use Xutim\EventBundle\Infra\Doctrine\ORM\EventRepository;

#[AutoconfigureTag('xutim.translation_stat_provider', ['priority' => 10])]
final readonly class EventTranslationStatProvider implements TranslationStatProvider
{
    public function __construct(
        private EventRepository $eventRepository,
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
            $count = $this->eventRepository->countUntranslatedForLocales([$locale]);
            if ($count > 0) {
                $localeBreakdown[] = new LocaleStat(
                    locale: $locale,
                    count: $count,
                    url: $this->router->generate('admin_event_list', [
                        '_content_locale' => $locale,
                        'col' => ['translationStatus' => 'missing', 'publicationStatus' => 'published'],
                    ]),
                );
                $totalCount += $count;
            }
        }

        return new TranslationStat(
            label: 'events',
            icon: 'tabler:calendar-event',
            untranslatedCount: $totalCount,
            outdatedCount: 0,
            listUrl: $this->router->generate('admin_event_list', ['col' => ['translationStatus' => 'missing', 'publicationStatus' => 'published']]),
            localeBreakdown: $localeBreakdown,
        );
    }
}
