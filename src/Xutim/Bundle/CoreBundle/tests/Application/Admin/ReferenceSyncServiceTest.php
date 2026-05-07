<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Tests\Application\Admin;

use App\Entity\Core\LogEvent;
use App\Factory\ArticleFactory;
use App\Factory\ContentTranslationFactory;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Xutim\CoreBundle\Domain\Event\ContentTranslation\ContentTranslationUpdatedEvent;
use Xutim\CoreBundle\Entity\ContentTranslation;
use Xutim\CoreBundle\Service\ReferenceSyncService;
use Zenstruck\Foundry\Test\Factories;

class ReferenceSyncServiceTest extends AdminApplicationTestCase
{
    use Factories;

    private ReferenceSyncService $syncService;
    private EntityManagerInterface $em;

    protected function setUp(): void
    {
        parent::setUp();
        self::bootKernel();
        $this->syncService = static::getContainer()->get(ReferenceSyncService::class);
        $this->em = static::getContainer()->get(EntityManagerInterface::class);
    }

    /**
     * Reference was changed then reverted (same content as snapshot).
     * Sibling's referenceSyncedAt should be bumped — no effective change.
     */
    public function testRevertedReferenceChangeAutoSyncsSiblings(): void
    {
        $article = ArticleFactory::createOne();
        $en = ContentTranslationFactory::createOne([
            'article' => $article,
            'locale' => 'en',
            'title' => 'Original Title',
            'content' => ['blocks' => [['type' => 'paragraph', 'data' => ['text' => 'Hello']]]],
        ]);
        $fr = ContentTranslationFactory::createOne(['article' => $article, 'locale' => 'fr']);

        $snapshotTime = new DateTimeImmutable('-2 hours');
        $this->createLogEvent($en, $snapshotTime, 'Original Title', $en->getContent());

        $fr->changeReferenceSyncedAt($snapshotTime);
        $this->forceUpdatedAt($en, new DateTimeImmutable('-1 hour'));
        $this->em->flush();

        $this->syncService->resyncRevertedSiblings($en);
        $this->em->flush();


        $this->assertEquals($en->getUpdatedAt(), $fr->getReferenceSyncedAt());
    }

    /**
     * Reference was changed with actual new content (different from snapshot).
     * Sibling's referenceSyncedAt should NOT be bumped — real change exists.
     */
    public function testRealReferenceChangeDoesNotSyncSiblings(): void
    {
        $article = ArticleFactory::createOne();
        $en = ContentTranslationFactory::createOne([
            'article' => $article,
            'locale' => 'en',
            'title' => 'New Title',
            'content' => ['blocks' => [['type' => 'paragraph', 'data' => ['text' => 'Updated']]]],
        ]);
        $fr = ContentTranslationFactory::createOne(['article' => $article, 'locale' => 'fr']);

        $snapshotTime = new DateTimeImmutable('-2 hours');
        $this->createLogEvent($en, $snapshotTime, 'Old Title', ['blocks' => [['type' => 'paragraph', 'data' => ['text' => 'Original']]]]);

        $fr->changeReferenceSyncedAt($snapshotTime);
        $this->forceUpdatedAt($en, new DateTimeImmutable('-1 hour'));
        $this->em->flush();

        $this->syncService->resyncRevertedSiblings($en);
        $this->em->flush();


        $this->assertEquals($snapshotTime, $fr->getReferenceSyncedAt());
    }

    /**
     * Sibling has null referenceSyncedAt — should be skipped,
     * not crash or auto-sync.
     */
    public function testNullReferenceSyncedAtIsSkipped(): void
    {
        $article = ArticleFactory::createOne();
        $en = ContentTranslationFactory::createOne(['article' => $article, 'locale' => 'en']);
        $fr = ContentTranslationFactory::createOne(['article' => $article, 'locale' => 'fr']);
        $this->em->flush();

        $this->assertNull($fr->getReferenceSyncedAt());

        $this->syncService->resyncRevertedSiblings($en);
        $this->em->flush();


        $this->assertNull($fr->getReferenceSyncedAt());
    }

    /**
     * Sibling's referenceSyncedAt is already >= reference updatedAt.
     * Already synced — should be skipped.
     */
    public function testAlreadySyncedSiblingIsSkipped(): void
    {
        $article = ArticleFactory::createOne();
        $en = ContentTranslationFactory::createOne(['article' => $article, 'locale' => 'en']);
        $fr = ContentTranslationFactory::createOne(['article' => $article, 'locale' => 'fr']);

        $syncTime = $en->getUpdatedAt();
        $fr->changeReferenceSyncedAt($syncTime);
        $this->em->flush();

        $this->syncService->resyncRevertedSiblings($en);
        $this->em->flush();


        $this->assertEquals($syncTime, $fr->getReferenceSyncedAt());
    }

    /**
     * No log event exists at sibling's referenceSyncedAt (reference was
     * created after the sibling). Should be skipped — nothing to compare.
     */
    public function testMissingSnapshotIsSkipped(): void
    {
        $article = ArticleFactory::createOne();
        $en = ContentTranslationFactory::createOne(['article' => $article, 'locale' => 'en']);
        $fr = ContentTranslationFactory::createOne(['article' => $article, 'locale' => 'fr']);

        $oldTime = new DateTimeImmutable('-3 days');
        $fr->changeReferenceSyncedAt($oldTime);
        $this->forceUpdatedAt($en, new DateTimeImmutable('-1 hour'));
        $this->em->flush();

        // No log event created at -3 days → findRevisionAtOrBefore returns null
        $this->syncService->resyncRevertedSiblings($en);
        $this->em->flush();


        $this->assertEquals($oldTime, $fr->getReferenceSyncedAt());
    }

    /**
     * Multiple siblings: one has reverted content, another has real changes.
     * Only the reverted one should be synced.
     */
    public function testOnlyRevertedSiblingsAreSynced(): void
    {
        $article = ArticleFactory::createOne();
        $en = ContentTranslationFactory::createOne([
            'article' => $article,
            'locale' => 'en',
            'title' => 'Current Title',
            'content' => ['blocks' => []],
        ]);
        $fr = ContentTranslationFactory::createOne(['article' => $article, 'locale' => 'fr']);
        $de = ContentTranslationFactory::createOne(['article' => $article, 'locale' => 'de']);

        $frSnapshotTime = new DateTimeImmutable('-3 hours');
        $deSnapshotTime = new DateTimeImmutable('-2 hours');

        // fr snapshot matches current en → reverted
        $this->createLogEvent($en, $frSnapshotTime, 'Current Title', ['blocks' => []]);
        // de snapshot has different content → real change
        $this->createLogEvent($en, $deSnapshotTime, 'Different Title', ['blocks' => []]);

        $fr->changeReferenceSyncedAt($frSnapshotTime);
        $de->changeReferenceSyncedAt($deSnapshotTime);
        $this->forceUpdatedAt($en, new DateTimeImmutable('-1 hour'));
        $this->em->flush();

        $this->syncService->resyncRevertedSiblings($en);
        $this->em->flush();




        $this->assertEquals($en->getUpdatedAt(), $fr->getReferenceSyncedAt(), 'fr should be synced (reverted)');
        $this->assertEquals($deSnapshotTime, $de->getReferenceSyncedAt(), 'de should NOT be synced (real change)');
    }

    /**
     * Sibling synced relative to the immediately-previous reference revision
     * gets bumped to the current reference updatedAt.
     */
    public function testMarkRecentlyOutdatedSiblingsBumpsRecentSync(): void
    {
        $article = ArticleFactory::createOne();
        $en = ContentTranslationFactory::createOne(['article' => $article, 'locale' => 'en']);
        $fr = ContentTranslationFactory::createOne(['article' => $article, 'locale' => 'fr']);

        $previousRevAt = new DateTimeImmutable('-1 hour');
        $this->createLogEvent($en, new DateTimeImmutable('-2 hours'), 'V1', ['blocks' => []]);
        $this->createLogEvent($en, $previousRevAt, 'V2', ['blocks' => []]);
        $this->createLogEvent($en, new DateTimeImmutable('-5 minutes'), 'V3', ['blocks' => []]);

        $fr->changeReferenceSyncedAt($previousRevAt);
        $this->em->flush();

        $count = $this->syncService->markRecentlyOutdatedSiblingsAsSynced($article);
        $this->em->flush();

        $this->assertSame(1, $count);
        $this->assertEquals($en->getUpdatedAt(), $fr->getReferenceSyncedAt());
    }

    /**
     * Sibling that was already stale before the latest reference edit
     * is left untouched — translator-pending work is preserved.
     */
    public function testMarkRecentlyOutdatedSiblingsLeavesPreviouslyStaleAlone(): void
    {
        $article = ArticleFactory::createOne();
        $en = ContentTranslationFactory::createOne(['article' => $article, 'locale' => 'en']);
        $fr = ContentTranslationFactory::createOne(['article' => $article, 'locale' => 'fr']);

        $this->createLogEvent($en, new DateTimeImmutable('-3 hours'), 'V1', ['blocks' => []]);
        $this->createLogEvent($en, new DateTimeImmutable('-1 hour'), 'V2', ['blocks' => []]);
        $this->createLogEvent($en, new DateTimeImmutable('-5 minutes'), 'V3', ['blocks' => []]);

        $stale = new DateTimeImmutable('-3 hours');
        $fr->changeReferenceSyncedAt($stale);
        $this->em->flush();

        $count = $this->syncService->markRecentlyOutdatedSiblingsAsSynced($article);
        $this->em->flush();

        $this->assertSame(0, $count);
        $this->assertEquals($stale, $fr->getReferenceSyncedAt());
    }

    /**
     * Sibling with null referenceSyncedAt is left alone — never been synced.
     */
    public function testMarkRecentlyOutdatedSiblingsSkipsNullSync(): void
    {
        $article = ArticleFactory::createOne();
        $en = ContentTranslationFactory::createOne(['article' => $article, 'locale' => 'en']);
        $fr = ContentTranslationFactory::createOne(['article' => $article, 'locale' => 'fr']);

        $this->createLogEvent($en, new DateTimeImmutable('-1 hour'), 'V1', ['blocks' => []]);
        $this->createLogEvent($en, new DateTimeImmutable('-5 minutes'), 'V2', ['blocks' => []]);
        $this->em->flush();

        $count = $this->syncService->markRecentlyOutdatedSiblingsAsSynced($article);
        $this->em->flush();

        $this->assertSame(0, $count);
        $this->assertNull($fr->getReferenceSyncedAt());
    }

    /**
     * With only one revision (just-created reference), there's no previous
     * revision to anchor against — service returns 0 and changes nothing.
     */
    public function testMarkRecentlyOutdatedSiblingsReturnsZeroWithNoPreviousRevision(): void
    {
        $article = ArticleFactory::createOne();
        $en = ContentTranslationFactory::createOne(['article' => $article, 'locale' => 'en']);
        $fr = ContentTranslationFactory::createOne(['article' => $article, 'locale' => 'fr']);

        $this->createLogEvent($en, new DateTimeImmutable('-5 minutes'), 'V1', ['blocks' => []]);
        $existing = new DateTimeImmutable('-10 minutes');
        $fr->changeReferenceSyncedAt($existing);
        $this->em->flush();

        $count = $this->syncService->markRecentlyOutdatedSiblingsAsSynced($article);
        $this->em->flush();

        $this->assertSame(0, $count);
        $this->assertEquals($existing, $fr->getReferenceSyncedAt());
    }

    private function createLogEvent(
        object $translation,
        DateTimeImmutable $recordedAt,
        string $title,
        array $content,
    ): void {
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
}
