<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Tests\Unit\MessageHandler;

use DateTimeImmutable;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Uid\Uuid;
use Xutim\CoreBundle\Context\SiteContext;
use Xutim\CoreBundle\Domain\Factory\ContentDraftFactory;
use Xutim\CoreBundle\Domain\Factory\LogEventFactory;
use Xutim\CoreBundle\Domain\Model\ArticleInterface;
use Xutim\CoreBundle\Domain\Model\ContentDraftInterface;
use Xutim\CoreBundle\Domain\Model\ContentTranslationInterface;
use Xutim\CoreBundle\Entity\LogEvent;
use Xutim\CoreBundle\Message\Command\ContentTranslation\EditContentTranslationCommand;
use Xutim\CoreBundle\MessageHandler\Command\ContentTranslation\EditContentTranslationHandler;
use Xutim\CoreBundle\Repository\ContentDraftRepository;
use Xutim\CoreBundle\Repository\ContentTranslationRepository;
use Xutim\CoreBundle\Repository\LogEventRepository;
use Xutim\CoreBundle\Service\ReferenceSyncService;
use Xutim\CoreBundle\Service\SearchContentBuilder;
use Xutim\Domain\DomainEvent;
use Xutim\SecurityBundle\Domain\Model\UserInterface;
use Xutim\SecurityBundle\Repository\UserRepositoryInterface;

final class EditContentTranslationHandlerTest extends TestCase
{
    public function testNoChangeOnPublishedTranslationSkipsDraftCreation(): void
    {
        $translationId = Uuid::v4();
        $translation = $this->publishedTranslation($translationId, 'Intro', 'Title', 'Sub', 'slug', [], 'Desc');

        $draftRepo = $this->createMock(ContentDraftRepository::class);
        $eventRepo = $this->createMock(LogEventRepository::class);
        $handler = $this->buildHandler(
            contentTransRepo: $this->contentTransRepoReturning($translation),
            draftRepo: $draftRepo,
            eventRepo: $eventRepo,
        );

        $draftRepo->method('findDraft')->willReturn(null);
        $draftRepo->expects($this->never())->method('save');
        $eventRepo->expects($this->never())->method('save');

        $handler(new EditContentTranslationCommand(
            $translationId, 'Intro', 'Title', 'Sub', 'slug', [], 'Desc', 'en', 'user@example.com',
        ));
    }

    public function testChangedContentOnPublishedTranslationCreatesDraft(): void
    {
        $translationId = Uuid::v4();
        $translation = $this->publishedTranslation($translationId, '', 'Old Title', '', 'slug', [], '');

        $draft = $this->createStub(ContentDraftInterface::class);
        $draft->method('getId')->willReturn(Uuid::v4());
        $draft->method('getTranslation')->willReturn($translation);
        $draft->method('getPreTitle')->willReturn('');
        $draft->method('getTitle')->willReturn('New Title');
        $draft->method('getSubTitle')->willReturn('');
        $draft->method('getSlug')->willReturn('slug');
        $draft->method('getContent')->willReturn([]);
        $draft->method('getDescription')->willReturn('');
        $draft->method('getCreatedAt')->willReturn(new DateTimeImmutable());

        $draftFactory = $this->createStub(ContentDraftFactory::class);
        $draftFactory->method('createUserDraft')->willReturn($draft);

        $draftRepo = $this->createMock(ContentDraftRepository::class);
        $draftRepo->method('findDraft')->willReturn(null);
        $eventRepo = $this->createMock(LogEventRepository::class);

        $handler = $this->buildHandler(
            contentTransRepo: $this->contentTransRepoReturning($translation),
            draftRepo: $draftRepo,
            eventRepo: $eventRepo,
            draftFactory: $draftFactory,
        );

        $draftRepo->expects($this->once())->method('save');
        $eventRepo->expects($this->once())->method('save');
        $draftRepo->expects($this->once())->method('flush');

        $handler(new EditContentTranslationCommand(
            $translationId, '', 'New Title', '', 'slug', [], '', 'en', 'user@example.com',
        ));
    }

    public function testNoChangeOnExistingDraftSkipsUpdate(): void
    {
        $translationId = Uuid::v4();
        $translation = $this->publishedTranslation($translationId, '', 'Published', '', 'slug', [], '');

        $draft = $this->createStub(ContentDraftInterface::class);
        $draft->method('getPreTitle')->willReturn('');
        $draft->method('getTitle')->willReturn('Draft Title');
        $draft->method('getSubTitle')->willReturn('');
        $draft->method('getSlug')->willReturn('slug');
        $draft->method('getContent')->willReturn([]);
        $draft->method('getDescription')->willReturn('');

        $draftRepo = $this->createMock(ContentDraftRepository::class);
        $draftRepo->method('findDraft')->willReturn($draft);
        $eventRepo = $this->createMock(LogEventRepository::class);

        $handler = $this->buildHandler(
            contentTransRepo: $this->contentTransRepoReturning($translation),
            draftRepo: $draftRepo,
            eventRepo: $eventRepo,
        );

        $draftRepo->expects($this->never())->method('save');
        $eventRepo->expects($this->never())->method('save');

        $handler(new EditContentTranslationCommand(
            $translationId, '', 'Draft Title', '', 'slug', [], '', 'en', 'user@example.com',
        ));
    }

    public function testContentBlockChangeDetected(): void
    {
        $translationId = Uuid::v4();
        $oldBlocks = ['blocks' => [['type' => 'paragraph', 'data' => ['text' => 'hello']]]];
        $newBlocks = ['blocks' => [['type' => 'paragraph', 'data' => ['text' => 'world']]]];
        $translation = $this->publishedTranslation($translationId, '', 'Title', '', 'slug', $oldBlocks, '');

        $draft = $this->createStub(ContentDraftInterface::class);
        $draft->method('getId')->willReturn(Uuid::v4());
        $draft->method('getTranslation')->willReturn($translation);
        $draft->method('getPreTitle')->willReturn('');
        $draft->method('getTitle')->willReturn('Title');
        $draft->method('getSubTitle')->willReturn('');
        $draft->method('getSlug')->willReturn('slug');
        $draft->method('getContent')->willReturn($newBlocks);
        $draft->method('getDescription')->willReturn('');
        $draft->method('getCreatedAt')->willReturn(new DateTimeImmutable());

        $draftFactory = $this->createStub(ContentDraftFactory::class);
        $draftFactory->method('createUserDraft')->willReturn($draft);

        $draftRepo = $this->createMock(ContentDraftRepository::class);
        $draftRepo->method('findDraft')->willReturn(null);

        $handler = $this->buildHandler(
            contentTransRepo: $this->contentTransRepoReturning($translation),
            draftRepo: $draftRepo,
            draftFactory: $draftFactory,
        );

        $draftRepo->expects($this->once())->method('save');

        $handler(new EditContentTranslationCommand(
            $translationId, '', 'Title', '', 'slug', $newBlocks, '', 'en', 'user@example.com',
        ));
    }

    private function buildHandler(
        ?ContentTranslationRepository $contentTransRepo = null,
        ?ContentDraftRepository $draftRepo = null,
        ?LogEventRepository $eventRepo = null,
        ?ContentDraftFactory $draftFactory = null,
    ): EditContentTranslationHandler {
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

        $userRepo = $this->createStub(UserRepositoryInterface::class);
        $userRepo->method('findOneByEmail')->willReturn($this->createStub(UserInterface::class));

        return new EditContentTranslationHandler(
            $logEventFactory,
            $contentTransRepo ?? $this->createStub(ContentTranslationRepository::class),
            $eventRepo ?? $this->createStub(LogEventRepository::class),
            $siteContext,
            $searchBuilder,
            $draftRepo ?? $this->createStub(ContentDraftRepository::class),
            $draftFactory ?? $this->createStub(ContentDraftFactory::class),
            $userRepo,
            $this->createStub(ReferenceSyncService::class),
        );
    }

    private function contentTransRepoReturning(ContentTranslationInterface $translation): ContentTranslationRepository
    {
        $repo = $this->createStub(ContentTranslationRepository::class);
        $repo->method('find')->willReturn($translation);

        return $repo;
    }

    private function publishedTranslation(
        Uuid $id,
        string $preTitle,
        string $title,
        string $subTitle,
        string $slug,
        array $content,
        string $description,
    ): ContentTranslationInterface {
        $translation = $this->createStub(ContentTranslationInterface::class);
        $translation->method('getId')->willReturn($id);
        $translation->method('isPublished')->willReturn(true);
        $translation->method('getPreTitle')->willReturn($preTitle);
        $translation->method('getTitle')->willReturn($title);
        $translation->method('getSubTitle')->willReturn($subTitle);
        $translation->method('getSlug')->willReturn($slug);
        $translation->method('getContent')->willReturn($content);
        $translation->method('getDescription')->willReturn($description);
        $translation->method('getLocale')->willReturn('en');

        $object = $this->createStub(ArticleInterface::class);
        $object->method('getTranslationByLocale')->willReturn(null);
        $translation->method('getObject')->willReturn($object);

        return $translation;
    }
}
