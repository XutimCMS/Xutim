<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Tests\Application\Admin;

use App\Entity\Core\ContentTranslation as AppContentTranslation;
use App\Entity\Core\LogEvent;
use App\Entity\Core\Page;
use App\Entity\Core\Tag;
use App\Entity\Core\TagTranslation;
use App\Entity\Media\Media;
use App\Entity\Media\MediaTranslation;
use App\Factory\ArticleFactory;
use App\Factory\ContentTranslationFactory;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Xutim\CoreBundle\Entity\Color;
use Xutim\CoreBundle\Domain\Event\ContentTranslation\ContentTranslationUpdatedEvent;
use Xutim\CoreBundle\Entity\ContentTranslation;
use Zenstruck\Foundry\Test\Factories;

final class RevisionDiffRenderingTest extends AdminApplicationTestCase
{
    use Factories;

    private KernelBrowser $client;
    private EntityManagerInterface $em;

    protected function setUp(): void
    {
        parent::setUp();
        $this->client = $this->createAuthenticatedClient();
        $this->em = static::getContainer()->get(EntityManagerInterface::class);
    }

    public function test_revision_page_renders_quote_and_list_diffs_in_html(): void
    {
        $article = ArticleFactory::createOne();
        $newContent = $this->loadFixtureContent('community_page_new');
        $translation = ContentTranslationFactory::createOne([
            'article' => $article,
            'locale' => 'en',
            'title' => 'Current title',
            'slug' => 'revision-diff-' . uniqid(),
            'content' => $newContent,
        ]);

        $oldRevision = $this->createLogEvent(
            $translation,
            new DateTimeImmutable('-2 hours'),
            'Current title',
            $this->loadFixtureContent('community_page_old'),
        );
        $newRevision = $this->createLogEvent(
            $translation,
            new DateTimeImmutable('-1 hour'),
            'Current title',
            $newContent,
        );

        $this->client->request(
            'GET',
            sprintf(
                '/admin/en/content-translation/revisions/%s/%s/%s',
                $translation->getId()->toRfc4122(),
                $oldRevision->getId()->toRfc4122(),
                $newRevision->getId()->toRfc4122(),
            )
        );

        $this->assertResponseIsSuccessful();
        $html = (string) $this->client->getResponse()->getContent();
        $this->assertStringContainsString('How to join', $html);
        $this->assertStringContainsString('Meet the team', $html);
        $this->assertSelectorExists('blockquote footer ins');
        $this->assertSelectorTextContains('blockquote footer', '2nd edition');
        $this->assertSelectorExists('.diff-inline ins');
        $this->assertStringContainsString('<ins>technical</ins>', $html);
    }

    public function test_reference_diff_renders_middle_removal_in_position(): void
    {
        $article = ArticleFactory::createOne();
        $newContent = $this->loadFixtureContent('reference_alignment_new');
        $referenceTranslation = ContentTranslationFactory::createOne([
            'article' => $article,
            'locale' => 'en',
            'title' => 'Reference',
            'slug' => 'reference-' . uniqid(),
            'content' => $newContent,
        ]);

        $siblingTranslation = ContentTranslationFactory::createOne([
            'article' => $article,
            'locale' => 'fr',
            'title' => 'Sibling',
            'slug' => 'sibling-' . uniqid(),
        ]);

        $snapshotTime = new DateTimeImmutable('-2 hours');
        $this->createLogEvent(
            $referenceTranslation,
            $snapshotTime,
            'Reference',
            $this->loadFixtureContent('reference_alignment_old'),
        );

        $siblingTranslation->changeReferenceSyncedAt($snapshotTime);
        $this->em->flush();

        $this->client->request(
            'GET',
            sprintf('/admin/en/content-translation/%s/reference-diff', $siblingTranslation->getId()->toRfc4122())
        );

        $this->assertResponseIsSuccessful();
        $html = (string) $this->client->getResponse()->getContent();
        $beforePos = strpos($html, 'Our platform serves thousands of users across multiple regions.');
        $removedPos = strpos($html, 'Each year we onboard new partners from Europe, Asia and the Americas.');
        $afterPos = strpos($html, 'We also run workshops for students and early-career professionals.');

        $this->assertNotFalse($beforePos);
        $this->assertNotFalse($removedPos);
        $this->assertNotFalse($afterPos);
        $this->assertTrue($beforePos < $removedPos);
        $this->assertTrue($removedPos < $afterPos);
    }

    public function test_revision_page_renders_image_row_diff(): void
    {
        $article = ArticleFactory::createOne();
        $newContent = $this->loadFixtureContent('image_row_new');
        $translation = ContentTranslationFactory::createOne([
            'article' => $article,
            'locale' => 'en',
            'title' => 'Gallery title',
            'slug' => 'gallery-diff-' . uniqid(),
            'content' => $newContent,
        ]);

        $oldRevision = $this->createLogEvent(
            $translation,
            new DateTimeImmutable('-2 hours'),
            'Gallery title',
            $this->loadFixtureContent('image_row_old'),
        );
        $newRevision = $this->createLogEvent(
            $translation,
            new DateTimeImmutable('-1 hour'),
            'Gallery title',
            $newContent,
        );

        $this->client->request(
            'GET',
            sprintf(
                '/admin/en/content-translation/revisions/%s/%s/%s',
                $translation->getId()->toRfc4122(),
                $oldRevision->getId()->toRfc4122(),
                $newRevision->getId()->toRfc4122(),
            )
        );

        $this->assertResponseIsSuccessful();
        $html = (string) $this->client->getResponse()->getContent();
        $this->assertStringContainsString('/media/variants/thumb_small/old-a.webp', $html);
        $this->assertStringContainsString('/media/variants/thumb_small/new-a.webp', $html);
        $this->assertStringContainsString('/media/variants/thumb_small/shared-b.webp', $html);
        $this->assertSelectorExists('img.img-thumbnail');
    }

    public function test_revision_page_renders_media_block_diffs(): void
    {
        $oldImage = $this->createMediaWithTranslation('old-image', 'Old Image');
        $newImage = $this->createMediaWithTranslation('new-image', 'New Image');
        $oldFile = $this->createMediaWithTranslation('old-file', 'Old File', 'pdf');
        $newFile = $this->createMediaWithTranslation('new-file', 'New File', 'pdf');

        $article = ArticleFactory::createOne();
        $translation = ContentTranslationFactory::createOne([
            'article' => $article,
            'locale' => 'en',
            'title' => 'Media diff',
            'slug' => 'media-diff-' . uniqid(),
            'content' => $this->loadFixtureContent('media_blocks_new', [
                'OLD_IMAGE_ID' => $oldImage->id()->toRfc4122(),
                'NEW_IMAGE_ID' => $newImage->id()->toRfc4122(),
                'OLD_FILE_ID' => $oldFile->id()->toRfc4122(),
                'NEW_FILE_ID' => $newFile->id()->toRfc4122(),
            ]),
        ]);

        $oldRevision = $this->createLogEvent(
            $translation,
            new DateTimeImmutable('-2 hours'),
            'Media diff',
            $this->loadFixtureContent('media_blocks_old', [
                'OLD_IMAGE_ID' => $oldImage->id()->toRfc4122(),
                'NEW_IMAGE_ID' => $newImage->id()->toRfc4122(),
                'OLD_FILE_ID' => $oldFile->id()->toRfc4122(),
                'NEW_FILE_ID' => $newFile->id()->toRfc4122(),
            ]),
        );
        $newRevision = $this->createLogEvent(
            $translation,
            new DateTimeImmutable('-1 hour'),
            'Media diff',
            $translation->getContent(),
        );

        $this->client->request(
            'GET',
            sprintf(
                '/admin/en/content-translation/revisions/%s/%s/%s',
                $translation->getId()->toRfc4122(),
                $oldRevision->getId()->toRfc4122(),
                $newRevision->getId()->toRfc4122(),
            )
        );

        $this->assertResponseIsSuccessful();
        $html = (string) $this->client->getResponse()->getContent();
        $this->assertStringContainsString('/media/uploads/old-image.jpg', $html);
        $this->assertStringContainsString('/media/uploads/new-image.jpg', $html);
        $this->assertStringContainsString('Old File', $html);
        $this->assertStringContainsString('New File', $html);
    }

    public function test_revision_page_renders_link_block_diffs(): void
    {
        $oldPage = $this->createPageWithTranslation('Old Page', 'old-page');
        $newPage = $this->createPageWithTranslation('New Page', 'new-page');
        $oldArticle = ArticleFactory::createOne();
        $newArticle = ArticleFactory::createOne();
        $oldArticleTrans = ContentTranslationFactory::createOne([
            'article' => $oldArticle,
            'locale' => 'en',
            'title' => 'Old Article',
            'slug' => 'old-article-' . uniqid(),
        ]);
        $newArticleTrans = ContentTranslationFactory::createOne([
            'article' => $newArticle,
            'locale' => 'en',
            'title' => 'New Article',
            'slug' => 'new-article-' . uniqid(),
        ]);
        $oldTag = $this->createTagWithTranslation('Old Tag', 'old-tag');
        $newTag = $this->createTagWithTranslation('New Tag', 'new-tag');

        $article = ArticleFactory::createOne();
        $translation = ContentTranslationFactory::createOne([
            'article' => $article,
            'locale' => 'en',
            'title' => 'Link diff',
            'slug' => 'link-diff-' . uniqid(),
            'content' => $this->loadFixtureContent('link_blocks_new', [
                'OLD_PAGE_ID' => $oldPage->getId()->toRfc4122(),
                'NEW_PAGE_ID' => $newPage->getId()->toRfc4122(),
                'OLD_ARTICLE_ID' => $oldArticle->getId()->toRfc4122(),
                'NEW_ARTICLE_ID' => $newArticle->getId()->toRfc4122(),
                'OLD_TAG_ID' => $oldTag->getId()->toRfc4122(),
                'NEW_TAG_ID' => $newTag->getId()->toRfc4122(),
            ]),
        ]);

        $oldRevision = $this->createLogEvent(
            $translation,
            new DateTimeImmutable('-2 hours'),
            'Link diff',
            $this->loadFixtureContent('link_blocks_old', [
                'OLD_PAGE_ID' => $oldPage->getId()->toRfc4122(),
                'NEW_PAGE_ID' => $newPage->getId()->toRfc4122(),
                'OLD_ARTICLE_ID' => $oldArticle->getId()->toRfc4122(),
                'NEW_ARTICLE_ID' => $newArticle->getId()->toRfc4122(),
                'OLD_TAG_ID' => $oldTag->getId()->toRfc4122(),
                'NEW_TAG_ID' => $newTag->getId()->toRfc4122(),
            ]),
        );
        $newRevision = $this->createLogEvent(
            $translation,
            new DateTimeImmutable('-1 hour'),
            'Link diff',
            $translation->getContent(),
        );

        $this->client->request(
            'GET',
            sprintf(
                '/admin/en/content-translation/revisions/%s/%s/%s',
                $translation->getId()->toRfc4122(),
                $oldRevision->getId()->toRfc4122(),
                $newRevision->getId()->toRfc4122(),
            )
        );

        $this->assertResponseIsSuccessful();
        $html = (string) $this->client->getResponse()->getContent();
        $this->assertStringContainsString('Old Page', $html);
        $this->assertStringContainsString('New Page', $html);
        $this->assertStringContainsString('Old Article', $html);
        $this->assertStringContainsString('New Article', $html);
        $this->assertStringContainsString('Old Tag', $html);
        $this->assertStringContainsString('New Tag', $html);
        $this->assertStringContainsString('compact', $html);
        $this->assertStringContainsString('cloud', $html);
    }

    public function test_revision_page_renders_misc_block_diffs(): void
    {
        $article = ArticleFactory::createOne();
        $translation = ContentTranslationFactory::createOne([
            'article' => $article,
            'locale' => 'en',
            'title' => 'Misc diff',
            'slug' => 'misc-diff-' . uniqid(),
            'content' => $this->loadFixtureContent('misc_blocks_new'),
        ]);

        $oldRevision = $this->createLogEvent(
            $translation,
            new DateTimeImmutable('-2 hours'),
            'Misc diff',
            $this->loadFixtureContent('misc_blocks_old'),
        );
        $newRevision = $this->createLogEvent(
            $translation,
            new DateTimeImmutable('-1 hour'),
            'Misc diff',
            $translation->getContent(),
        );

        $this->client->request(
            'GET',
            sprintf(
                '/admin/en/content-translation/revisions/%s/%s/%s',
                $translation->getId()->toRfc4122(),
                $oldRevision->getId()->toRfc4122(),
                $newRevision->getId()->toRfc4122(),
            )
        );

        $this->assertResponseIsSuccessful();
        $html = (string) $this->client->getResponse()->getContent();
        $this->assertStringContainsString('new-snippet-code', $html);
        $this->assertStringContainsString('https://example.com/new', $html);
        $this->assertStringContainsString('Updated embed caption', $html);
        $this->assertStringContainsString('Foldable', $html);
        $this->assertStringContainsString('revised', $html);
        $this->assertStringContainsString('* * *', $html);
    }

    public function test_revision_page_renders_checklist_list_diffs_without_crashing(): void
    {
        $article = ArticleFactory::createOne();
        $newContent = $this->loadFixtureContent('checklist_list_new');
        $translation = ContentTranslationFactory::createOne([
            'article' => $article,
            'locale' => 'en',
            'title' => 'Checklist diff',
            'slug' => 'checklist-diff-' . uniqid(),
            'content' => $newContent,
        ]);

        $oldRevision = $this->createLogEvent(
            $translation,
            new DateTimeImmutable('-2 hours'),
            'Checklist diff',
            $this->loadFixtureContent('checklist_list_old'),
        );
        $newRevision = $this->createLogEvent(
            $translation,
            new DateTimeImmutable('-1 hour'),
            'Checklist diff',
            $newContent,
        );

        $this->client->request(
            'GET',
            sprintf(
                '/admin/en/content-translation/revisions/%s/%s/%s',
                $translation->getId()->toRfc4122(),
                $oldRevision->getId()->toRfc4122(),
                $newRevision->getId()->toRfc4122(),
            )
        );

        $this->assertResponseIsSuccessful();
        $html = (string) $this->client->getResponse()->getContent();
        $this->assertStringContainsString('Pack bags', $html);
        $this->assertStringContainsString('carefully', $html);
        $this->assertStringContainsString('Collect candles', $html);
        $this->assertStringContainsString('matches', $html);
        $this->assertSelectorExists('.diff-inline ins');
    }

    public function test_revision_page_renders_unknown_block_fallback_metadata(): void
    {
        $article = ArticleFactory::createOne();
        $newContent = $this->loadFixtureContent('unknown_block_new');
        $translation = ContentTranslationFactory::createOne([
            'article' => $article,
            'locale' => 'en',
            'title' => 'Unknown diff',
            'slug' => 'unknown-diff-' . uniqid(),
            'content' => $newContent,
        ]);

        $oldRevision = $this->createLogEvent(
            $translation,
            new DateTimeImmutable('-2 hours'),
            'Unknown diff',
            $this->loadFixtureContent('unknown_block_old'),
        );
        $newRevision = $this->createLogEvent(
            $translation,
            new DateTimeImmutable('-1 hour'),
            'Unknown diff',
            $newContent,
        );

        $this->client->request(
            'GET',
            sprintf(
                '/admin/en/content-translation/revisions/%s/%s/%s',
                $translation->getId()->toRfc4122(),
                $oldRevision->getId()->toRfc4122(),
                $newRevision->getId()->toRfc4122(),
            )
        );

        $this->assertResponseIsSuccessful();
        $html = (string) $this->client->getResponse()->getContent();
        $this->assertStringContainsString('Unknown block', $html);
    }

    public function test_revision_page_survives_malformed_historical_payloads(): void
    {
        $article = ArticleFactory::createOne();
        $newContent = $this->loadFixtureContent('malformed_history_new');
        $translation = ContentTranslationFactory::createOne([
            'article' => $article,
            'locale' => 'en',
            'title' => 'Malformed history diff',
            'slug' => 'malformed-history-diff-' . uniqid(),
            'content' => $newContent,
        ]);

        $oldRevision = $this->createLogEvent(
            $translation,
            new DateTimeImmutable('-2 hours'),
            'Malformed history diff',
            $this->loadFixtureContent('malformed_history_old'),
        );
        $newRevision = $this->createLogEvent(
            $translation,
            new DateTimeImmutable('-1 hour'),
            'Malformed history diff',
            $newContent,
        );

        $this->client->request(
            'GET',
            sprintf(
                '/admin/en/content-translation/revisions/%s/%s/%s',
                $translation->getId()->toRfc4122(),
                $oldRevision->getId()->toRfc4122(),
                $newRevision->getId()->toRfc4122(),
            )
        );

        $this->assertResponseIsSuccessful();
        $html = (string) $this->client->getResponse()->getContent();
        $this->assertStringContainsString('Valid text before malformed', $html);
        $this->assertStringContainsString('history revised', $html);
        $this->assertStringContainsString('Valid text after malformed', $html);
        $this->assertStringContainsString('history remains', $html);
    }

    private function createLogEvent(
        object $translation,
        DateTimeImmutable $recordedAt,
        string $title,
        array $content,
    ): LogEvent {
        $event = new ContentTranslationUpdatedEvent(
            $translation->getId(),
            '',
            $title,
            '',
            $translation->getSlug(),
            $content,
            '',
            $translation->getLocale(),
            $translation->getCreatedAt(),
        );

        $log = new LogEvent(
            $translation->getId(),
            'test@example.com',
            ContentTranslation::class,
            $event,
        );

        $this->forceRecordedAt($log, $recordedAt);
        $this->em->persist($log);
        $this->em->flush();

        return $log;
    }

    private function forceRecordedAt(LogEvent $log, DateTimeImmutable $at): void
    {
        $class = new \ReflectionClass($log);
        do {
            if ($class->hasProperty('recordedAt')) {
                $class->getProperty('recordedAt')->setValue($log, $at);
                return;
            }
        } while ($class = $class->getParentClass());
    }

    private function loadFixtureContent(string $name, array $replacements = []): array
    {
        $fixturePath = __DIR__ . '/Fixtures/RevisionDiff/' . $name . '.json';
        $json = file_get_contents($fixturePath);
        self::assertNotFalse($json, sprintf('Failed to read fixture "%s".', $fixturePath));

        if ($replacements !== []) {
            $json = strtr($json, $replacements);
        }

        $data = json_decode($json, true, 512, JSON_THROW_ON_ERROR);
        self::assertIsArray($data);

        return $data;
    }

    private function createPageWithTranslation(string $title, string $slug): Page
    {
        $page = new Page(null, ['en'], null, null);
        $translation = new AppContentTranslation('', $title, '', $slug, ['blocks' => []], 'en', '', $page, null);
        $this->em->persist($page);
        $this->em->persist($translation);
        $this->em->flush();

        return $page;
    }

    private function createTagWithTranslation(string $name, string $slug): Tag
    {
        $tag = new Tag(new Color('ef7d01'), null, null);
        $translation = new TagTranslation($name, $slug, 'en', $tag);
        $tag->addTranslation($translation);
        $this->em->persist($tag);
        $this->em->persist($translation);
        $this->em->flush();

        return $tag;
    }

    private function createMediaWithTranslation(string $baseName, string $name, string $extension = 'jpg'): Media
    {
        $mime = $extension === 'pdf' ? 'application/pdf' : 'image/jpeg';
        $media = new Media(
            null,
            'uploads/' . $baseName . '.' . $extension,
            $extension,
            $mime,
            hash('sha256', $baseName),
            1024,
            $extension === 'pdf' ? 0 : 800,
            $extension === 'pdf' ? 0 : 600,
            null,
            $baseName
        );
        $translation = new MediaTranslation($media, 'en', $name, $name);
        $media->addTranslation($translation);
        $this->em->persist($media);
        $this->em->persist($translation);
        $this->em->flush();

        return $media;
    }
}
