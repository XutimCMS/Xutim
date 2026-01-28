<?php

declare(strict_types=1);

namespace Xutim\EditorBundle\Tests\Unit\Domain\Factory;

use PHPUnit\Framework\TestCase;
use Xutim\CoreBundle\Domain\Model\ContentTranslationInterface;
use Xutim\EditorBundle\Domain\Factory\ContentDraftFactory;
use Xutim\EditorBundle\Entity\ContentDraft;
use Xutim\CoreBundle\Entity\DraftStatus;
use Xutim\SecurityBundle\Domain\Model\UserInterface;

final class ContentDraftFactoryTest extends TestCase
{
    private ContentDraftFactory $factory;

    protected function setUp(): void
    {
        $this->factory = new ContentDraftFactory(ContentDraft::class);
    }

    public function testCreate(): void
    {
        $translation = $this->createStub(ContentTranslationInterface::class);

        $draft = $this->factory->create($translation);

        $this->assertInstanceOf(ContentDraft::class, $draft);
        $this->assertSame($translation, $draft->getTranslation());
        $this->assertNull($draft->getUser());
        $this->assertSame(DraftStatus::LIVE, $draft->getStatus());
    }

    public function testCreateWithUser(): void
    {
        $translation = $this->createStub(ContentTranslationInterface::class);
        $user = $this->createStub(UserInterface::class);

        $draft = $this->factory->create($translation, $user);

        $this->assertSame($user, $draft->getUser());
        $this->assertSame(DraftStatus::EDITING, $draft->getStatus());
    }

    public function testCreateWithBasedOnDraft(): void
    {
        $translation = $this->createStub(ContentTranslationInterface::class);
        $user = $this->createStub(UserInterface::class);
        $basedOn = new ContentDraft($translation);

        $draft = $this->factory->create($translation, $user, $basedOn);

        $this->assertSame($basedOn, $draft->getBasedOnDraft());
    }

    public function testCreateLiveVersion(): void
    {
        $translation = $this->createStub(ContentTranslationInterface::class);

        $draft = $this->factory->createLiveVersion($translation);

        $this->assertNull($draft->getUser());
        $this->assertSame(DraftStatus::LIVE, $draft->getStatus());
        $this->assertNull($draft->getBasedOnDraft());
    }

    public function testCreateUserDraft(): void
    {
        $translation = $this->createStub(ContentTranslationInterface::class);
        $user = $this->createStub(UserInterface::class);
        $liveDraft = new ContentDraft($translation);

        $userDraft = $this->factory->createUserDraft($translation, $user, $liveDraft);

        $this->assertSame($user, $userDraft->getUser());
        $this->assertSame($liveDraft, $userDraft->getBasedOnDraft());
        $this->assertSame(DraftStatus::EDITING, $userDraft->getStatus());
    }
}
