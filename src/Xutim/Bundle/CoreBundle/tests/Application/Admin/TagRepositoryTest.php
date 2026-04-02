<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Tests\Application\Admin;

use App\Factory\TagFactory;
use App\Factory\TagTranslationFactory;
use Xutim\CoreBundle\Dto\Admin\FilterDto;
use Xutim\CoreBundle\Entity\PublicationStatus;
use Xutim\CoreBundle\Repository\TagRepository;
use Zenstruck\Foundry\Test\Factories;

class TagRepositoryTest extends AdminApplicationTestCase
{
    use Factories;

    private TagRepository $tagRepo;

    protected function setUp(): void
    {
        parent::setUp();
        self::bootKernel();
        $this->tagRepo = static::getContainer()->get(TagRepository::class);
    }

    public function testNoFilterReturnsAllTags(): void
    {
        $tag = TagFactory::createOne();
        TagTranslationFactory::createOne(['tag' => $tag, 'locale' => 'en']);

        $results = $this->query(new FilterDto(), 'en');
        $this->assertTagIn($tag, $results);
    }

    public function testSearchTermMatchesName(): void
    {
        $tag = TagFactory::createOne();
        TagTranslationFactory::createOne(['tag' => $tag, 'locale' => 'en', 'name' => 'Spirituality']);

        $filter = new FilterDto(searchTerm: 'spirit');
        $results = $this->query($filter, 'en');
        $this->assertTagIn($tag, $results);
    }

    public function testSearchTermNoMatch(): void
    {
        $tag = TagFactory::createOne();
        TagTranslationFactory::createOne(['tag' => $tag, 'locale' => 'en', 'name' => 'Spirituality']);

        $filter = new FilterDto(searchTerm: 'zzzznotfound');
        $results = $this->query($filter, 'en');
        $this->assertTagNotIn($tag, $results);
    }

    public function testSearchFallsBackToDefaultLocale(): void
    {
        $tag = TagFactory::createOne();
        TagTranslationFactory::createOne(['tag' => $tag, 'locale' => 'en', 'name' => 'Community']);

        $filter = new FilterDto(searchTerm: 'commun');
        $results = $this->query($filter, 'fr');
        $this->assertTagIn($tag, $results);
    }

    public function testFilterByPublicationStatusDraft(): void
    {
        $tag = TagFactory::createOne();
        TagTranslationFactory::createOne(['tag' => $tag, 'locale' => 'en']);

        $published = TagFactory::createOne();
        $published->changeStatus(PublicationStatus::Published);
        TagTranslationFactory::createOne(['tag' => $published, 'locale' => 'en']);
        $this->flush();

        $filter = new FilterDto(cols: ['publicationStatus' => 'draft']);
        $results = $this->query($filter, 'en');
        $this->assertTagIn($tag, $results);
        $this->assertTagNotIn($published, $results);
    }

    public function testFilterByPublicationStatusPublished(): void
    {
        $draft = TagFactory::createOne();
        TagTranslationFactory::createOne(['tag' => $draft, 'locale' => 'en']);

        $published = TagFactory::createOne();
        $published->changeStatus(PublicationStatus::Published);
        TagTranslationFactory::createOne(['tag' => $published, 'locale' => 'en']);
        $this->flush();

        $filter = new FilterDto(cols: ['publicationStatus' => 'published']);
        $results = $this->query($filter, 'en');
        $this->assertTagIn($published, $results);
        $this->assertTagNotIn($draft, $results);
    }

    public function testFilterTranslationStatusTranslated(): void
    {
        $translated = TagFactory::createOne();
        TagTranslationFactory::createOne(['tag' => $translated, 'locale' => 'es']);

        $notTranslated = TagFactory::createOne();
        TagTranslationFactory::createOne(['tag' => $notTranslated, 'locale' => 'en']);

        $filter = new FilterDto(cols: ['translationStatus' => 'translated']);
        $results = $this->query($filter, 'es');
        $this->assertTagIn($translated, $results);
        $this->assertTagNotIn($notTranslated, $results);
    }

    public function testFilterTranslationStatusMissingIncludesFallback(): void
    {
        $onlyDefault = TagFactory::createOne();
        TagTranslationFactory::createOne(['tag' => $onlyDefault, 'locale' => 'en']);

        $translated = TagFactory::createOne();
        TagTranslationFactory::createOne(['tag' => $translated, 'locale' => 'en']);
        TagTranslationFactory::createOne(['tag' => $translated, 'locale' => 'es']);

        $filter = new FilterDto(cols: ['translationStatus' => 'missing']);
        $results = $this->query($filter, 'es');
        $this->assertTagIn($onlyDefault, $results);
        $this->assertTagNotIn($translated, $results);
    }

    public function testFilterTranslationStatusMissingNoTranslationAtAll(): void
    {
        $onlyFrench = TagFactory::createOne();
        TagTranslationFactory::createOne(['tag' => $onlyFrench, 'locale' => 'fr']);

        $filter = new FilterDto(cols: ['translationStatus' => 'missing']);
        $results = $this->query($filter, 'es');
        $this->assertTagIn($onlyFrench, $results);
    }

    public function testFilterByName(): void
    {
        $match = TagFactory::createOne();
        TagTranslationFactory::createOne(['tag' => $match, 'locale' => 'en', 'name' => 'Weekly prayer']);

        $noMatch = TagFactory::createOne();
        TagTranslationFactory::createOne(['tag' => $noMatch, 'locale' => 'en', 'name' => 'Music']);

        $filter = new FilterDto(cols: ['name' => 'prayer']);
        $results = $this->query($filter, 'en');
        $this->assertTagIn($match, $results);
        $this->assertTagNotIn($noMatch, $results);
    }

    public function testFilterByExcludeFromNewsTrue(): void
    {
        $excluded = TagFactory::createOne();
        $excluded->toggleExcludeFromNews();
        TagTranslationFactory::createOne(['tag' => $excluded, 'locale' => 'en']);

        $included = TagFactory::createOne();
        TagTranslationFactory::createOne(['tag' => $included, 'locale' => 'en']);
        $this->flush();

        $filter = new FilterDto(cols: ['excludeFromNews' => 'true']);
        $results = $this->query($filter, 'en');
        $this->assertTagIn($excluded, $results);
        $this->assertTagNotIn($included, $results);
    }

    public function testFilterByExcludeFromNewsFalse(): void
    {
        $excluded = TagFactory::createOne();
        $excluded->toggleExcludeFromNews();
        TagTranslationFactory::createOne(['tag' => $excluded, 'locale' => 'en']);

        $included = TagFactory::createOne();
        TagTranslationFactory::createOne(['tag' => $included, 'locale' => 'en']);
        $this->flush();

        $filter = new FilterDto(cols: ['excludeFromNews' => 'false']);
        $results = $this->query($filter, 'en');
        $this->assertTagIn($included, $results);
        $this->assertTagNotIn($excluded, $results);
    }

    public function testFilterByUpdatedAtLast7Days(): void
    {
        $recent = TagFactory::createOne();
        $recentTrans = TagTranslationFactory::createOne(['tag' => $recent, 'locale' => 'en']);

        $old = TagFactory::createOne();
        $oldTrans = TagTranslationFactory::createOne(['tag' => $old, 'locale' => 'en']);
        $this->forceUpdatedAt($oldTrans, new \DateTimeImmutable('-30 days'));
        $this->flush();

        $filter = new FilterDto(cols: ['updatedAt' => '7']);
        $results = $this->query($filter, 'en');
        $this->assertTagIn($recent, $results);
        $this->assertTagNotIn($old, $results);
    }

    public function testFilterByUpdatedAtOlderThan90Days(): void
    {
        $recent = TagFactory::createOne();
        TagTranslationFactory::createOne(['tag' => $recent, 'locale' => 'en']);

        $old = TagFactory::createOne();
        $oldTrans = TagTranslationFactory::createOne(['tag' => $old, 'locale' => 'en']);
        $this->forceUpdatedAt($oldTrans, new \DateTimeImmutable('-120 days'));
        $this->flush();

        $filter = new FilterDto(cols: ['updatedAt' => '90+']);
        $results = $this->query($filter, 'en');
        $this->assertTagIn($old, $results);
        $this->assertTagNotIn($recent, $results);
    }

    public function testCombinedFilters(): void
    {
        $match = TagFactory::createOne();
        $match->changeStatus(PublicationStatus::Published);
        TagTranslationFactory::createOne(['tag' => $match, 'locale' => 'en', 'name' => 'Reflection']);
        $this->flush();

        $wrongStatus = TagFactory::createOne();
        TagTranslationFactory::createOne(['tag' => $wrongStatus, 'locale' => 'en', 'name' => 'Reflection draft']);

        $wrongName = TagFactory::createOne();
        $wrongName->changeStatus(PublicationStatus::Published);
        TagTranslationFactory::createOne(['tag' => $wrongName, 'locale' => 'en', 'name' => 'Music']);
        $this->flush();

        $filter = new FilterDto(cols: ['publicationStatus' => 'published', 'name' => 'reflection']);
        $results = $this->query($filter, 'en');
        $this->assertTagIn($match, $results);
        $this->assertTagNotIn($wrongStatus, $results);
        $this->assertTagNotIn($wrongName, $results);
    }

    /**
     * @return list<object>
     */
    private function query(FilterDto $filter, string $locale): array
    {
        return $this->tagRepo->queryByFilter($filter, $locale)->getQuery()->getResult();
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

    private function assertTagIn(object $tag, array $results): void
    {
        $id = $tag->getId()->toRfc4122();
        $ids = array_map(fn ($t) => $t->getId()->toRfc4122(), $results);
        $this->assertContains($id, $ids);
    }

    private function assertTagNotIn(object $tag, array $results): void
    {
        $id = $tag->getId()->toRfc4122();
        $ids = array_map(fn ($t) => $t->getId()->toRfc4122(), $results);
        $this->assertNotContains($id, $ids);
    }
}
