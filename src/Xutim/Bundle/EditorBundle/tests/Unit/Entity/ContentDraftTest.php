<?php

declare(strict_types=1);

namespace Xutim\EditorBundle\Tests\Unit\Entity;

use PHPUnit\Framework\TestCase;
use Xutim\CoreBundle\Domain\Model\ContentTranslationInterface;
use Xutim\EditorBundle\Domain\Model\ContentDraftInterface;
use Xutim\EditorBundle\Entity\ContentDraft;
use Xutim\CoreBundle\Entity\DraftStatus;
use Xutim\SecurityBundle\Domain\Model\UserInterface;

final class ContentDraftTest extends TestCase
{
    public function testCanInstantiateLiveDraft(): void
    {
        $translation = $this->createStub(ContentTranslationInterface::class);

        $draft = new ContentDraft($translation);

        $this->assertInstanceOf(ContentDraftInterface::class, $draft);
        $this->assertSame($translation, $draft->getTranslation());
        $this->assertNull($draft->getUser());
        $this->assertTrue($draft->isLiveVersion());
        $this->assertSame(DraftStatus::LIVE, $draft->getStatus());
        $this->assertNull($draft->getBasedOnDraft());
        $this->assertCount(0, $draft->getBlocks());
    }

    public function testCanInstantiateUserDraft(): void
    {
        $translation = $this->createStub(ContentTranslationInterface::class);
        $user = $this->createStub(UserInterface::class);

        $draft = new ContentDraft($translation, $user);

        $this->assertSame($translation, $draft->getTranslation());
        $this->assertSame($user, $draft->getUser());
        $this->assertFalse($draft->isLiveVersion());
        $this->assertSame(DraftStatus::EDITING, $draft->getStatus());
    }

    public function testCanInstantiateUserDraftBasedOnLive(): void
    {
        $translation = $this->createStub(ContentTranslationInterface::class);
        $user = $this->createStub(UserInterface::class);
        $liveDraft = new ContentDraft($translation);

        $userDraft = new ContentDraft($translation, $user, $liveDraft);

        $this->assertSame($liveDraft, $userDraft->getBasedOnDraft());
        $this->assertSame(DraftStatus::EDITING, $userDraft->getStatus());
    }

    public function testChangeStatus(): void
    {
        $translation = $this->createStub(ContentTranslationInterface::class);
        $draft = new ContentDraft($translation);

        $draft->changeStatus(DraftStatus::STALE);

        $this->assertSame(DraftStatus::STALE, $draft->getStatus());
    }

    public function testMarkAsLive(): void
    {
        $translation = $this->createStub(ContentTranslationInterface::class);
        $user = $this->createStub(UserInterface::class);
        $draft = new ContentDraft($translation, $user);

        $this->assertSame(DraftStatus::EDITING, $draft->getStatus());

        $draft->markAsLive();

        $this->assertSame(DraftStatus::LIVE, $draft->getStatus());
    }

    public function testMarkAsStale(): void
    {
        $translation = $this->createStub(ContentTranslationInterface::class);
        $user = $this->createStub(UserInterface::class);
        $draft = new ContentDraft($translation, $user);

        $draft->markAsStale();

        $this->assertSame(DraftStatus::STALE, $draft->getStatus());
    }

    public function testMarkAsDiscarded(): void
    {
        $translation = $this->createStub(ContentTranslationInterface::class);
        $user = $this->createStub(UserInterface::class);
        $draft = new ContentDraft($translation, $user);

        $draft->markAsDiscarded();

        $this->assertSame(DraftStatus::DISCARDED, $draft->getStatus());
    }

    public function testGetTopLevelBlocksReturnsOnlyBlocksWithoutParent(): void
    {
        $translation = $this->createStub(ContentTranslationInterface::class);
        $draft = new ContentDraft($translation);

        $this->assertCount(0, $draft->getTopLevelBlocks());
    }

    public function testUpdatesTimestamp(): void
    {
        $translation = $this->createStub(ContentTranslationInterface::class);
        $draft = new ContentDraft($translation);

        $originalUpdatedAt = $draft->getUpdatedAt();

        usleep(1000);
        $draft->updates();

        $this->assertGreaterThan($originalUpdatedAt, $draft->getUpdatedAt());
    }
}
