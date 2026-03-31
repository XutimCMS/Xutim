<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Tests\Unit\MessageHandler;

use DateTimeImmutable;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Uid\Uuid;
use Xutim\CoreBundle\Context\SiteContext;
use Xutim\CoreBundle\Domain\Factory\LogEventFactory;
use Xutim\CoreBundle\Domain\Model\ArticleInterface;
use Xutim\CoreBundle\Domain\Model\ContentDraftInterface;
use Xutim\CoreBundle\Domain\Model\ContentTranslationInterface;
use Xutim\CoreBundle\Domain\Model\LogEventInterface;
use Xutim\CoreBundle\Entity\LogEvent;
use Xutim\CoreBundle\Message\Command\ContentDraft\PublishContentDraftCommand;
use Xutim\CoreBundle\MessageHandler\Command\ContentDraft\PublishContentDraftHandler;
use Xutim\CoreBundle\Repository\ContentDraftRepository;
use Xutim\CoreBundle\Repository\ContentTranslationRepository;
use Xutim\CoreBundle\Repository\LogEventRepository;
use Xutim\CoreBundle\Service\ReferenceSyncService;
use Xutim\CoreBundle\Service\SearchContentBuilder;
use Xutim\Domain\DomainEvent;

final class PublishContentDraftHandlerTest extends TestCase
{
    public function testPublishRemovesDraftAndSavesEventBeforeFlush(): void
    {
        $draftRepo = $this->createMock(ContentDraftRepository::class);
        $contentTransRepo = $this->createMock(ContentTranslationRepository::class);
        $eventRepo = $this->createMock(LogEventRepository::class);
        $handler = $this->buildHandler($draftRepo, $contentTransRepo, $eventRepo);

        $translation = $this->translationStub();
        $draft = $this->draftStub($translation);
        $draftRepo->method('find')->willReturn($draft);
        $eventRepo->method('findContentRevisionsByTranslation')->willReturn([$this->createStub(LogEventInterface::class)]);

        $callOrder = [];

        $draftRepo->expects($this->once())->method('remove')
            ->willReturnCallback(function () use (&$callOrder) { $callOrder[] = 'draft_remove'; });
        $contentTransRepo->expects($this->once())->method('save')
            ->willReturnCallback(function () use (&$callOrder) { $callOrder[] = 'translation_save'; });
        $eventRepo->expects($this->once())->method('save')
            ->willReturnCallback(function () use (&$callOrder) { $callOrder[] = 'event_save'; });
        $draftRepo->expects($this->once())->method('flush')
            ->willReturnCallback(function () use (&$callOrder) { $callOrder[] = 'flush'; });

        $handler(new PublishContentDraftCommand(Uuid::v4(), 'user@example.com'));

        $this->assertSame(['draft_remove', 'translation_save', 'event_save', 'flush'], $callOrder);
    }

    public function testPublishRecordsContentTranslationUpdatedEvent(): void
    {
        $draftRepo = $this->createStub(ContentDraftRepository::class);
        $eventRepo = $this->createMock(LogEventRepository::class);
        $handler = $this->buildHandler($draftRepo, $this->createStub(ContentTranslationRepository::class), $eventRepo);

        $translation = $this->translationStub();
        $draft = $this->draftStub($translation);
        $draftRepo->method('find')->willReturn($draft);
        $eventRepo->method('findContentRevisionsByTranslation')->willReturn([$this->createStub(LogEventInterface::class)]);

        $savedLog = null;
        $eventRepo->expects($this->once())->method('save')
            ->willReturnCallback(function (LogEventInterface $log) use (&$savedLog) { $savedLog = $log; });

        $handler(new PublishContentDraftCommand(Uuid::v4(), 'user@example.com'));

        $this->assertNotNull($savedLog);
        $this->assertSame('user@example.com', $savedLog->getUserIdentifier());
    }

    private function buildHandler(
        ContentDraftRepository $draftRepo,
        ContentTranslationRepository $contentTransRepo,
        LogEventRepository $eventRepo,
    ): PublishContentDraftHandler {
        $logEventFactory = $this->createStub(LogEventFactory::class);
        $logEventFactory->method('create')->willReturnCallback(
            fn (Uuid $id, string $user, string $entity, DomainEvent $event) =>
                new LogEvent($id, $user, $entity, $event)
        );

        $siteContext = $this->createStub(SiteContext::class);
        $siteContext->method('getReferenceLocale')->willReturn('en');

        $searchBuilder = $this->createStub(SearchContentBuilder::class);
        $searchBuilder->method('build')->willReturn('');
        $searchBuilder->method('buildTagContent')->willReturn('');

        return new PublishContentDraftHandler(
            $draftRepo,
            $contentTransRepo,
            $logEventFactory,
            $eventRepo,
            $siteContext,
            $searchBuilder,
            $this->createStub(ReferenceSyncService::class),
        );
    }

    private function translationStub(): ContentTranslationInterface
    {
        $translation = $this->createStub(ContentTranslationInterface::class);
        $translation->method('getId')->willReturn(Uuid::v4());
        $translation->method('getLocale')->willReturn('en');
        $translation->method('getPreTitle')->willReturn('');
        $translation->method('getTitle')->willReturn('Title');
        $translation->method('getSubTitle')->willReturn('');
        $translation->method('getSlug')->willReturn('slug');
        $translation->method('getContent')->willReturn([]);
        $translation->method('getDescription')->willReturn('');
        $translation->method('getUpdatedAt')->willReturn(new DateTimeImmutable());
        $translation->method('getCreatedAt')->willReturn(new DateTimeImmutable());

        $object = $this->createStub(ArticleInterface::class);
        $object->method('getTranslationByLocale')->willReturn(null);
        $translation->method('getObject')->willReturn($object);

        return $translation;
    }

    private function draftStub(ContentTranslationInterface $translation): ContentDraftInterface
    {
        $draft = $this->createStub(ContentDraftInterface::class);
        $draft->method('getId')->willReturn(Uuid::v4());
        $draft->method('getTranslation')->willReturn($translation);

        return $draft;
    }
}
