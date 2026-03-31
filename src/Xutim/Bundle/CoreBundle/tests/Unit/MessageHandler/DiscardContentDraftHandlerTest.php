<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Tests\Unit\MessageHandler;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Uid\Uuid;
use Xutim\CoreBundle\Domain\Event\ContentDraft\ContentDraftDiscardedEvent;
use Xutim\CoreBundle\Domain\Factory\LogEventFactory;
use Xutim\CoreBundle\Domain\Model\ContentDraftInterface;
use Xutim\CoreBundle\Domain\Model\ContentTranslationInterface;
use Xutim\CoreBundle\Domain\Model\LogEventInterface;
use Xutim\CoreBundle\Entity\LogEvent;
use Xutim\CoreBundle\Message\Command\ContentDraft\DiscardContentDraftCommand;
use Xutim\CoreBundle\MessageHandler\Command\ContentDraft\DiscardContentDraftHandler;
use Xutim\CoreBundle\Repository\ContentDraftRepository;
use Xutim\CoreBundle\Repository\LogEventRepository;
use Xutim\Domain\DomainEvent;

final class DiscardContentDraftHandlerTest extends TestCase
{
    public function testDiscardRemovesDraftAndSavesEventBeforeFlush(): void
    {
        [$draftRepo, $eventRepo, $handler] = $this->createHandler();

        $this->configureDraftRepo($draftRepo);

        $callOrder = [];

        $draftRepo->expects($this->once())->method('remove')
            ->willReturnCallback(function () use (&$callOrder) { $callOrder[] = 'draft_remove'; });
        $eventRepo->expects($this->once())->method('save')
            ->willReturnCallback(function () use (&$callOrder) { $callOrder[] = 'event_save'; });
        $draftRepo->expects($this->once())->method('flush')
            ->willReturnCallback(function () use (&$callOrder) { $callOrder[] = 'flush'; });

        $handler(new DiscardContentDraftCommand(Uuid::v4(), 'user@example.com'));

        $this->assertSame(['draft_remove', 'event_save', 'flush'], $callOrder);
    }

    public function testDiscardRecordsContentDraftDiscardedEvent(): void
    {
        $draftRepo = $this->createStub(ContentDraftRepository::class);
        $eventRepo = $this->createMock(LogEventRepository::class);
        $handler = $this->buildHandler($draftRepo, $eventRepo);

        $this->configureDraftRepo($draftRepo);

        $savedLog = null;
        $eventRepo->expects($this->once())->method('save')
            ->willReturnCallback(function (LogEventInterface $log) use (&$savedLog) { $savedLog = $log; });

        $handler(new DiscardContentDraftCommand(Uuid::v4(), 'user@example.com'));

        $this->assertNotNull($savedLog);
        $this->assertInstanceOf(ContentDraftDiscardedEvent::class, $savedLog->getEvent());
        $this->assertSame('user@example.com', $savedLog->getUserIdentifier());
    }

    private function configureDraftRepo(ContentDraftRepository $draftRepo): void
    {
        $translation = $this->createStub(ContentTranslationInterface::class);
        $translation->method('getId')->willReturn(Uuid::v4());

        $draft = $this->createStub(ContentDraftInterface::class);
        $draft->method('getId')->willReturn(Uuid::v4());
        $draft->method('getTranslation')->willReturn($translation);

        $draftRepo->method('find')->willReturn($draft);
    }

    /**
     * @return array{ContentDraftRepository&\PHPUnit\Framework\MockObject\MockObject, LogEventRepository&\PHPUnit\Framework\MockObject\MockObject, DiscardContentDraftHandler}
     */
    private function createHandler(): array
    {
        $draftRepo = $this->createMock(ContentDraftRepository::class);
        $eventRepo = $this->createMock(LogEventRepository::class);

        return [$draftRepo, $eventRepo, $this->buildHandler($draftRepo, $eventRepo)];
    }

    private function buildHandler(ContentDraftRepository $draftRepo, LogEventRepository $eventRepo): DiscardContentDraftHandler
    {
        $logEventFactory = $this->createStub(LogEventFactory::class);
        $logEventFactory->method('create')->willReturnCallback(
            fn (Uuid $id, string $user, string $entity, DomainEvent $event) =>
                new LogEvent($id, $user, $entity, $event)
        );

        return new DiscardContentDraftHandler($draftRepo, $logEventFactory, $eventRepo);
    }
}
