<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Tests\Application\Admin;

use App\Factory\EventFactory;
use App\Factory\EventTranslationFactory;
use Xutim\CoreBundle\Dto\Admin\FilterDto;
use Xutim\CoreBundle\Entity\PublicationStatus;
use Xutim\EventBundle\Infra\Doctrine\ORM\EventRepository;
use Zenstruck\Foundry\Test\Factories;

class EventRepositoryTest extends AdminApplicationTestCase
{
    use Factories;

    private EventRepository $eventRepo;

    protected function setUp(): void
    {
        parent::setUp();
        self::bootKernel();
        $this->eventRepo = static::getContainer()->get(EventRepository::class);
    }

    public function testNoFilterReturnsAllEvents(): void
    {
        $event = EventFactory::createOne();
        EventTranslationFactory::createOne(['event' => $event, 'locale' => 'en']);

        $results = $this->query(new FilterDto(), 'en');
        $this->assertEventIn($event, $results);
    }

    public function testSearchTermMatchesTitle(): void
    {
        $event = EventFactory::createOne();
        EventTranslationFactory::createOne(['event' => $event, 'locale' => 'en', 'title' => 'Summer gathering']);

        $filter = new FilterDto(searchTerm: 'summer');
        $results = $this->query($filter, 'en');
        $this->assertEventIn($event, $results);
    }

    public function testSearchTermMatchesLocation(): void
    {
        $event = EventFactory::createOne();
        EventTranslationFactory::createOne(['event' => $event, 'locale' => 'en', 'location' => 'Berlin']);

        $filter = new FilterDto(searchTerm: 'berlin');
        $results = $this->query($filter, 'en');
        $this->assertEventIn($event, $results);
    }

    public function testSearchTermNoMatch(): void
    {
        $event = EventFactory::createOne();
        EventTranslationFactory::createOne(['event' => $event, 'locale' => 'en', 'title' => 'Summer gathering']);

        $filter = new FilterDto(searchTerm: 'zzzznotfound');
        $results = $this->query($filter, 'en');
        $this->assertEventNotIn($event, $results);
    }

    public function testSearchFallsBackToDefaultLocale(): void
    {
        $event = EventFactory::createOne();
        EventTranslationFactory::createOne(['event' => $event, 'locale' => 'en', 'title' => 'Autumn retreat']);

        $filter = new FilterDto(searchTerm: 'autumn');
        $results = $this->query($filter, 'fr');
        $this->assertEventIn($event, $results);
    }

    public function testFilterByPublicationStatusDraft(): void
    {
        $draft = EventFactory::createOne();
        EventTranslationFactory::createOne(['event' => $draft, 'locale' => 'en']);

        $published = EventFactory::createOne();
        $published->changeStatus(PublicationStatus::Published);
        EventTranslationFactory::createOne(['event' => $published, 'locale' => 'en']);
        $this->flush();

        $filter = new FilterDto(cols: ['publicationStatus' => 'draft']);
        $results = $this->query($filter, 'en');
        $this->assertEventIn($draft, $results);
        $this->assertEventNotIn($published, $results);
    }

    public function testFilterByPublicationStatusPublished(): void
    {
        $draft = EventFactory::createOne();
        EventTranslationFactory::createOne(['event' => $draft, 'locale' => 'en']);

        $published = EventFactory::createOne();
        $published->changeStatus(PublicationStatus::Published);
        EventTranslationFactory::createOne(['event' => $published, 'locale' => 'en']);
        $this->flush();

        $filter = new FilterDto(cols: ['publicationStatus' => 'published']);
        $results = $this->query($filter, 'en');
        $this->assertEventIn($published, $results);
        $this->assertEventNotIn($draft, $results);
    }

    public function testFilterTranslationStatusTranslated(): void
    {
        $translated = EventFactory::createOne();
        EventTranslationFactory::createOne(['event' => $translated, 'locale' => 'es']);

        $notTranslated = EventFactory::createOne();
        EventTranslationFactory::createOne(['event' => $notTranslated, 'locale' => 'en']);

        $filter = new FilterDto(cols: ['translationStatus' => 'translated']);
        $results = $this->query($filter, 'es');
        $this->assertEventIn($translated, $results);
        $this->assertEventNotIn($notTranslated, $results);
    }

    public function testFilterTranslationStatusMissingIncludesFallback(): void
    {
        $onlyDefault = EventFactory::createOne();
        EventTranslationFactory::createOne(['event' => $onlyDefault, 'locale' => 'en']);

        $translated = EventFactory::createOne();
        EventTranslationFactory::createOne(['event' => $translated, 'locale' => 'en']);
        EventTranslationFactory::createOne(['event' => $translated, 'locale' => 'es']);

        $filter = new FilterDto(cols: ['translationStatus' => 'missing']);
        $results = $this->query($filter, 'es');
        $this->assertEventIn($onlyDefault, $results);
        $this->assertEventNotIn($translated, $results);
    }

    public function testFilterTranslationStatusMissingNoTranslationAtAll(): void
    {
        $onlyFrench = EventFactory::createOne();
        EventTranslationFactory::createOne(['event' => $onlyFrench, 'locale' => 'fr']);

        $filter = new FilterDto(cols: ['translationStatus' => 'missing']);
        $results = $this->query($filter, 'es');
        $this->assertEventIn($onlyFrench, $results);
    }

    public function testFilterTranslationStatusMissingExcludesPastEvents(): void
    {
        $pastEvent = EventFactory::createOne([
            'startsAt' => new \DateTimeImmutable('-10 days'),
            'endsAt' => new \DateTimeImmutable('-5 days'),
        ]);
        EventTranslationFactory::createOne(['event' => $pastEvent, 'locale' => 'en']);

        $futureEvent = EventFactory::createOne([
            'startsAt' => new \DateTimeImmutable('+1 day'),
            'endsAt' => new \DateTimeImmutable('+2 days'),
        ]);
        EventTranslationFactory::createOne(['event' => $futureEvent, 'locale' => 'en']);

        $filter = new FilterDto(cols: ['translationStatus' => 'missing']);
        $results = $this->query($filter, 'es');
        $this->assertEventIn($futureEvent, $results);
        $this->assertEventNotIn($pastEvent, $results);
    }

    public function testFilterByTitle(): void
    {
        $match = EventFactory::createOne();
        EventTranslationFactory::createOne(['event' => $match, 'locale' => 'en', 'title' => 'Youth meeting']);

        $noMatch = EventFactory::createOne();
        EventTranslationFactory::createOne(['event' => $noMatch, 'locale' => 'en', 'title' => 'Concert']);

        $filter = new FilterDto(cols: ['title' => 'youth']);
        $results = $this->query($filter, 'en');
        $this->assertEventIn($match, $results);
        $this->assertEventNotIn($noMatch, $results);
    }

    public function testFilterByLocation(): void
    {
        $match = EventFactory::createOne();
        EventTranslationFactory::createOne(['event' => $match, 'locale' => 'en', 'location' => 'Hamburg']);

        $noMatch = EventFactory::createOne();
        EventTranslationFactory::createOne(['event' => $noMatch, 'locale' => 'en', 'location' => 'Paris']);

        $filter = new FilterDto(cols: ['location' => 'hamburg']);
        $results = $this->query($filter, 'en');
        $this->assertEventIn($match, $results);
        $this->assertEventNotIn($noMatch, $results);
    }

    public function testFilterByStartsAtUpcoming(): void
    {
        $future = EventFactory::createOne(['startsAt' => new \DateTimeImmutable('+5 days'), 'endsAt' => new \DateTimeImmutable('+6 days')]);
        EventTranslationFactory::createOne(['event' => $future, 'locale' => 'en']);

        $past = EventFactory::createOne(['startsAt' => new \DateTimeImmutable('-30 days'), 'endsAt' => new \DateTimeImmutable('-29 days')]);
        EventTranslationFactory::createOne(['event' => $past, 'locale' => 'en']);

        $filter = new FilterDto(cols: ['startsAt' => 'upcoming']);
        $results = $this->query($filter, 'en');
        $this->assertEventIn($future, $results);
        $this->assertEventNotIn($past, $results);
    }

    public function testFilterByUpdatedAtLast7Days(): void
    {
        $recent = EventFactory::createOne();
        EventTranslationFactory::createOne(['event' => $recent, 'locale' => 'en']);

        $old = EventFactory::createOne();
        $oldTrans = EventTranslationFactory::createOne(['event' => $old, 'locale' => 'en']);
        $this->forceUpdatedAt($oldTrans, new \DateTimeImmutable('-30 days'));
        $this->flush();

        $filter = new FilterDto(cols: ['updatedAt' => '7']);
        $results = $this->query($filter, 'en');
        $this->assertEventIn($recent, $results);
        $this->assertEventNotIn($old, $results);
    }

    public function testCombinedFilters(): void
    {
        $match = EventFactory::createOne();
        $match->changeStatus(PublicationStatus::Published);
        EventTranslationFactory::createOne(['event' => $match, 'locale' => 'en', 'title' => 'Workshop']);
        $this->flush();

        $wrongStatus = EventFactory::createOne();
        EventTranslationFactory::createOne(['event' => $wrongStatus, 'locale' => 'en', 'title' => 'Workshop draft']);

        $wrongTitle = EventFactory::createOne();
        $wrongTitle->changeStatus(PublicationStatus::Published);
        EventTranslationFactory::createOne(['event' => $wrongTitle, 'locale' => 'en', 'title' => 'Concert']);
        $this->flush();

        $filter = new FilterDto(cols: ['publicationStatus' => 'published', 'title' => 'workshop']);
        $results = $this->query($filter, 'en');
        $this->assertEventIn($match, $results);
        $this->assertEventNotIn($wrongStatus, $results);
        $this->assertEventNotIn($wrongTitle, $results);
    }

    /**
     * @return list<object>
     */
    private function query(FilterDto $filter, string $locale): array
    {
        return $this->eventRepo->queryByFilter($filter, $locale)->getQuery()->getResult();
    }

    private function forceUpdatedAt(object $entity, \DateTimeImmutable $at): void
    {
        $class = new \ReflectionClass($entity);
        do {
            if ($class->hasProperty('updatedAt')) {
                $class->getProperty('updatedAt')->setValue($entity, $at);
                return;
            }
        } while ($class = $class->getParentClass());

        throw new \RuntimeException('Property updatedAt not found');
    }

    private function flush(): void
    {
        static::getContainer()->get('doctrine.orm.entity_manager')->flush();
    }

    private function assertEventIn(object $event, array $results): void
    {
        $id = $event->getId()->toRfc4122();
        $ids = array_map(fn ($e) => $e->getId()->toRfc4122(), $results);
        $this->assertContains($id, $ids);
    }

    private function assertEventNotIn(object $event, array $results): void
    {
        $id = $event->getId()->toRfc4122();
        $ids = array_map(fn ($e) => $e->getId()->toRfc4122(), $results);
        $this->assertNotContains($id, $ids);
    }
}
