<?php

declare(strict_types=1);

namespace Xutim\EventBundle\Dashboard;

use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;
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

        return new TranslationStat(
            label: 'events',
            icon: 'tabler:calendar-event',
            untranslatedCount: $this->eventRepository->countUntranslatedForLocales($localesWithoutReference),
            outdatedCount: 0,
            listUrl: $this->router->generate('admin_event_list'),
        );
    }
}
