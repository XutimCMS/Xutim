<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Tests\Application\Admin;

use App\Entity\Security\User;
use App\Factory\ArticleFactory;
use App\Factory\ContentTranslationFactory;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Component\Uid\Uuid;
use Xutim\SecurityBundle\Security\UserRoles;
use Zenstruck\Foundry\Test\Factories;

class TranslatorDashboardTest extends AdminApplicationTestCase
{
    use Factories;

    private function em(): EntityManagerInterface
    {
        return static::getContainer()->get(EntityManagerInterface::class);
    }

    /**
     * Untranslated article with a missing allowed locale should appear
     * in the "Newest untranslated articles" table.
     */
    public function testUntranslatedArticleAppearsOnDashboard(): void
    {
        $article = ArticleFactory::createOne();
        ContentTranslationFactory::createOne(['article' => $article, 'locale' => 'en', 'title' => 'Untranslated Article']);
        $this->em()->flush();

        $crawler = $this->requestDashboard(['en', 'fr']);

        $frame = $crawler->filter('turbo-frame#untranslated-articles');
        $this->assertStringContainsString('Untranslated Article', $frame->html());
    }

    /**
     * Fully translated article should NOT appear in untranslated table.
     */
    public function testFullyTranslatedArticleDoesNotAppearOnDashboard(): void
    {
        $article = ArticleFactory::createOne();
        ContentTranslationFactory::createOne(['article' => $article, 'locale' => 'en', 'title' => 'Complete Article']);
        ContentTranslationFactory::createOne(['article' => $article, 'locale' => 'fr']);
        $this->em()->flush();

        $crawler = $this->requestDashboard(['en', 'fr']);

        $frame = $crawler->filter('turbo-frame#untranslated-articles');
        $this->assertStringNotContainsString('Complete Article', $frame->html());
    }

    /**
     * Outdated translation should appear in "New version of translated
     * articles" table.
     */
    public function testOutdatedArticleAppearsInChangedTable(): void
    {
        $article = ArticleFactory::createOne();
        $en = ContentTranslationFactory::createOne(['article' => $article, 'locale' => 'en', 'title' => 'Changed Article']);
        $fr = ContentTranslationFactory::createOne(['article' => $article, 'locale' => 'fr']);

        $fr->changeReferenceSyncedAt(new DateTimeImmutable('-2 hours'));
        $this->forceUpdatedAt($en, new DateTimeImmutable('-1 hour'));
        $this->em()->flush();

        $crawler = $this->requestDashboard(['en', 'fr']);

        $frame = $crawler->filter('turbo-frame#changed-articles');
        $this->assertStringContainsString('Changed Article', $frame->html());
    }

    /**
     * Untranslated article link should use _content_locale of the first
     * missing allowed locale, not the default en.
     */
    public function testUntranslatedArticleLinkUsesCorrectLocale(): void
    {
        $article = ArticleFactory::createOne();
        ContentTranslationFactory::createOne(['article' => $article, 'locale' => 'en', 'title' => 'Link Test']);
        $this->em()->flush();

        $crawler = $this->requestDashboard(['en', 'fr']);

        $link = $crawler->filter('turbo-frame#untranslated-articles a[href*="' . $article->getId()->toRfc4122() . '"]');
        $this->assertStringContainsString('/admin/fr/article/edit/', $link->attr('href'));
    }

    /**
     * Changed article link should use _content_locale of the first
     * outdated non-reference locale.
     */
    public function testChangedArticleLinkUsesCorrectLocale(): void
    {
        $article = ArticleFactory::createOne();
        $en = ContentTranslationFactory::createOne(['article' => $article, 'locale' => 'en', 'title' => 'Changed Link Test']);
        $fr = ContentTranslationFactory::createOne(['article' => $article, 'locale' => 'fr']);

        $fr->changeReferenceSyncedAt(new DateTimeImmutable('-2 hours'));
        $this->forceUpdatedAt($en, new DateTimeImmutable('-1 hour'));
        $this->em()->flush();

        $crawler = $this->requestDashboard(['en', 'fr']);

        $link = $crawler->filter('turbo-frame#changed-articles a[href*="' . $article->getId()->toRfc4122() . '"]');
        $this->assertStringContainsString('/admin/fr/article/edit/', $link->attr('href'));
    }

    /**
     * Translator with 1 locale should NOT see the "Missing Languages"
     * or "Languages Changed" columns.
     */
    public function testLanguageColumnsHiddenForSingleLocaleTranslator(): void
    {
        $article = ArticleFactory::createOne();
        ContentTranslationFactory::createOne(['article' => $article, 'locale' => 'en', 'title' => 'Single Locale']);
        $this->em()->flush();

        $crawler = $this->requestDashboard(['fr']);

        $this->assertStringNotContainsString('Missing languages', $crawler->html());
        $this->assertStringNotContainsString('Languages changed', $crawler->html());
    }

    /**
     * Translator with 2+ locales should see the language columns.
     */
    public function testLanguageColumnsVisibleForMultiLocaleTranslator(): void
    {
        $article = ArticleFactory::createOne();
        ContentTranslationFactory::createOne(['article' => $article, 'locale' => 'en']);
        $this->em()->flush();

        $crawler = $this->requestDashboard(['en', 'fr']);

        $frame = $crawler->filter('turbo-frame#untranslated-articles');
        $this->assertStringContainsString('Missing languages', $frame->html());
    }

    /**
     * Both tables should be wrapped in turbo-frames for independent
     * pagination.
     */
    public function testDashboardTablesHaveTurboFrames(): void
    {
        $crawler = $this->requestDashboard(['en', 'fr']);

        $this->assertCount(1, $crawler->filter('turbo-frame#untranslated-articles'));
        $this->assertCount(1, $crawler->filter('turbo-frame#changed-articles'));
    }

    /**
     * Pagination footer should not appear when there are fewer than 10
     * articles.
     */
    public function testNoPaginationWhenFewArticles(): void
    {
        $article = ArticleFactory::createOne();
        ContentTranslationFactory::createOne(['article' => $article, 'locale' => 'en']);
        $this->em()->flush();

        $crawler = $this->requestDashboard(['en', 'fr']);

        $frame = $crawler->filter('turbo-frame#untranslated-articles');
        $this->assertStringNotContainsString('card-footer', $frame->html());
    }

    /**
     * Pagination footer should appear when there are more than 10 articles.
     */
    public function testPaginationAppearsWhenMoreThanTenArticles(): void
    {
        for ($i = 0; $i < 12; $i++) {
            $article = ArticleFactory::createOne();
            ContentTranslationFactory::createOne(['article' => $article, 'locale' => 'en']);
        }
        $this->em()->flush();

        $crawler = $this->requestDashboard(['en', 'fr']);

        $frame = $crawler->filter('turbo-frame#untranslated-articles');
        $this->assertStringContainsString('card-footer', $frame->html());
    }

    /**
     * Article links inside turbo-frames should have data-turbo-frame="_top"
     * to break out of the frame on navigation.
     */
    public function testArticleLinksBreakOutOfTurboFrame(): void
    {
        $article = ArticleFactory::createOne();
        ContentTranslationFactory::createOne(['article' => $article, 'locale' => 'en', 'title' => 'Turbo Test']);
        $this->em()->flush();

        $crawler = $this->requestDashboard(['en', 'fr']);

        $link = $crawler->filter('turbo-frame#untranslated-articles a[data-turbo-frame="_top"]');
        $this->assertGreaterThan(0, $link->count());
    }

    private function requestDashboard(array $translationLocales): \Symfony\Component\DomCrawler\Crawler
    {
        $user = $this->createTranslatorUser($translationLocales);
        self::ensureKernelShutdown();
        $client = static::createClient();
        $client->loginUser($user);

        $client->followRedirects();
        $crawler = $client->request('GET', '/admin/en/');
        $this->assertResponseIsSuccessful();

        return $crawler;
    }

    private function createTranslatorUser(array $locales): User
    {
        $user = new User(
            Uuid::v4(),
            'translator-' . uniqid() . '@example.test',
            'Test Translator',
            '$argon2id$v=19$m=65536,t=4,p=1$QjFsYXJXQU4xQk5vV0lWWg$95bL/Imstq/ZVmI1lxeyKzqLqW8CFB7winNkw/Ut0/I',
            [UserRoles::ROLE_TRANSLATOR],
            $locales,
            '',
        );

        $this->em()->persist($user);
        $this->em()->flush();

        return $user;
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
    }
}
