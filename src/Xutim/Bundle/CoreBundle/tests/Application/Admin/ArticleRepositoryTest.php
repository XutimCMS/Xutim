<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Tests\Application\Admin;

use App\Factory\ArticleFactory;
use App\Factory\ContentTranslationFactory;
use DateTimeImmutable;
use Xutim\CoreBundle\Repository\ArticleRepository;
use Zenstruck\Foundry\Test\Factories;

class ArticleRepositoryTest extends AdminApplicationTestCase
{
    use Factories;

    private ArticleRepository $articleRepo;

    protected function setUp(): void
    {
        parent::setUp();
        self::bootKernel();
        $this->articleRepo = static::getContainer()->get(ArticleRepository::class);
    }

    /**
     * Article has en only, fr is allowed and missing.
     * Should appear in untranslated list.
     */
    public function testArticleWithMissingAllowedLocaleAppears(): void
    {
        $article = ArticleFactory::createOne();
        ContentTranslationFactory::createOne(['article' => $article, 'locale' => 'en']);

        $results = $this->articleRepo->findByMissingTranslations(['en', 'fr']);
        $this->assertArticleIn($article, $results);
    }

    /**
     * Article has en and fr.
     * Should not appear — fully translated for user's locales.
     */
    public function testFullyTranslatedArticleDoesNotAppear(): void
    {
        $article = ArticleFactory::createOne();
        ContentTranslationFactory::createOne(['article' => $article, 'locale' => 'en']);
        ContentTranslationFactory::createOne(['article' => $article, 'locale' => 'fr']);

        $results = $this->articleRepo->findByMissingTranslations(['en', 'fr']);
        $this->assertArticleNotIn($article, $results);
    }

    /**
     * Article has en only, translationLocales = [en, de], fr not allowed.
     * Translator has [en, fr] but fr is disallowed → should not appear.
     */
    public function testArticleMissingOnlyDisallowedLocaleDoesNotAppear(): void
    {
        $article = ArticleFactory::new()->withRestrictedLocales(['en', 'de'])->create();
        ContentTranslationFactory::createOne(['article' => $article, 'locale' => 'en']);

        $results = $this->articleRepo->findByMissingTranslations(['en', 'fr']);
        $this->assertArticleNotIn($article, $results);
    }

    /**
     * Article has en only, translationLocales = [en, fr, de].
     * fr is allowed and missing → should appear.
     */
    public function testArticleMissingAllowedLocaleWithRestrictedLocalesAppears(): void
    {
        $article = ArticleFactory::new()->withRestrictedLocales(['en', 'fr', 'de'])->create();
        ContentTranslationFactory::createOne(['article' => $article, 'locale' => 'en']);

        $results = $this->articleRepo->findByMissingTranslations(['en', 'fr']);
        $this->assertArticleIn($article, $results);
    }

    /**
     * allTranslationLocales = true, has en only.
     * Should appear for translator with [en, fr].
     */
    public function testArticleWithAllLocalesEnabledAndMissingLocaleAppears(): void
    {
        $article = ArticleFactory::createOne();
        ContentTranslationFactory::createOne(['article' => $article, 'locale' => 'en']);

        $results = $this->articleRepo->findByMissingTranslations(['en', 'fr']);
        $this->assertArticleIn($article, $results);
    }

    /**
     * Article allows [en, fr], both exist. Translator has [en, fr, ro] but
     * ro is not allowed for this article. Only missing locale is disallowed.
     * Should not appear.
     */
    public function testArticleWhereOnlyMissingLocalesAreDisallowedDoesNotAppear(): void
    {
        $article = ArticleFactory::new()->withRestrictedLocales(['en', 'fr'])->create();
        ContentTranslationFactory::createOne(['article' => $article, 'locale' => 'en']);
        ContentTranslationFactory::createOne(['article' => $article, 'locale' => 'fr']);

        $results = $this->articleRepo->findByMissingTranslations(['en', 'fr', 'ro']);
        $this->assertArticleNotIn($article, $results);
    }

    /**
     * fr.referenceSyncedAt < en.updatedAt.
     * Reference was updated after fr was last synced → should appear.
     */
    public function testOutdatedTranslationAppears(): void
    {
        $article = ArticleFactory::createOne();
        $en = ContentTranslationFactory::createOne(['article' => $article, 'locale' => 'en']);
        $fr = ContentTranslationFactory::createOne(['article' => $article, 'locale' => 'fr']);

        $fr->changeReferenceSyncedAt(new DateTimeImmutable('-2 hours'));
        $this->forceUpdatedAt($en, new DateTimeImmutable('-1 hour'));
        $this->flush();

        $results = $this->articleRepo->findByChangedDefaultTranslations(['en', 'fr']);
        $this->assertArticleIn($article, $results);
    }

    /**
     * fr.referenceSyncedAt == en.updatedAt.
     * Translation is up to date → should not appear.
     */
    public function testSyncedTranslationDoesNotAppear(): void
    {
        $article = ArticleFactory::createOne();
        $en = ContentTranslationFactory::createOne(['article' => $article, 'locale' => 'en']);
        $fr = ContentTranslationFactory::createOne(['article' => $article, 'locale' => 'fr']);

        $syncTime = new DateTimeImmutable('-1 hour');
        $fr->changeReferenceSyncedAt($syncTime);
        $this->forceUpdatedAt($en, $syncTime);
        $this->flush();

        $results = $this->articleRepo->findByChangedDefaultTranslations(['en', 'fr']);
        $this->assertArticleNotIn($article, $results);
    }

    /**
     * en is both a user locale and the reference locale.
     * en.referenceSyncedAt < en.updatedAt but en should not be compared
     * against itself — the reference can't be "out of sync with itself".
     */
    public function testReferenceLocaleNotComparedAgainstItself(): void
    {
        $article = ArticleFactory::createOne();
        $en = ContentTranslationFactory::createOne(['article' => $article, 'locale' => 'en']);

        $en->changeReferenceSyncedAt(new DateTimeImmutable('-2 days'));
        $this->forceUpdatedAt($en, new DateTimeImmutable('-1 hour'));
        $this->flush();

        $results = $this->articleRepo->findByChangedDefaultTranslations(['en', 'fr']);
        $this->assertArticleNotIn($article, $results);
    }

    /**
     * fr.referenceSyncedAt is null — translation was never synced.
     * Should appear as outdated.
     */
    public function testNullReferenceSyncedAtAppearsAsOutdated(): void
    {
        $article = ArticleFactory::createOne();
        ContentTranslationFactory::createOne(['article' => $article, 'locale' => 'en']);
        $fr = ContentTranslationFactory::createOne(['article' => $article, 'locale' => 'fr']);

        $this->assertNull($fr->getReferenceSyncedAt());

        $results = $this->articleRepo->findByChangedDefaultTranslations(['en', 'fr']);
        $this->assertArticleIn($article, $results);
    }

    /**
     * Article has fr (synced) and de (outdated). Translator has [en, fr, de].
     * Should appear because de is outdated, even though fr is up to date.
     */
    public function testArticleAppearsWhenAtLeastOneNonReferenceLocaleIsOutdated(): void
    {
        $article = ArticleFactory::createOne();
        $en = ContentTranslationFactory::createOne(['article' => $article, 'locale' => 'en']);
        $fr = ContentTranslationFactory::createOne(['article' => $article, 'locale' => 'fr']);
        $de = ContentTranslationFactory::createOne(['article' => $article, 'locale' => 'de']);

        $syncTime = new DateTimeImmutable('-1 hour');
        $this->forceUpdatedAt($en, $syncTime);
        $fr->changeReferenceSyncedAt($syncTime);
        $de->changeReferenceSyncedAt(new DateTimeImmutable('-2 hours'));
        $this->flush();

        $results = $this->articleRepo->findByChangedDefaultTranslations(['en', 'fr', 'de']);
        $this->assertArticleIn($article, $results);
    }

    private function forceUpdatedAt(object $entity, DateTimeImmutable $at): void
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

    private function assertArticleIn(object $article, array $results): void
    {
        $id = $article->getId()->toRfc4122();
        $ids = array_map(fn ($a) => $a->getId()->toRfc4122(), $results);
        $this->assertContains($id, $ids);
    }

    private function assertArticleNotIn(object $article, array $results): void
    {
        $id = $article->getId()->toRfc4122();
        $ids = array_map(fn ($a) => $a->getId()->toRfc4122(), $results);
        $this->assertNotContains($id, $ids);
    }
}
