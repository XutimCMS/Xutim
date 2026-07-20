<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Tests\Application\Admin;

use App\Factory\ArticleFactory;
use App\Factory\ContentTranslationFactory;
use Symfony\Component\Messenger\MessageBusInterface;
use Xutim\CoreBundle\Message\Command\ContentTranslation\CreateContentTranslationCommand;
use Xutim\CoreBundle\Repository\ContentTranslationRepository;
use Zenstruck\Foundry\Test\Factories;

class CreateContentTranslationHandlerTest extends AdminApplicationTestCase
{
    use Factories;

    /**
     * Article created with fr first, then en (reference) is created.
     * Existing fr translation should have referenceSyncedAt set to
     * en.updatedAt so it doesn't show a false "reference changed" banner.
     */
    public function testCreatingReferenceTranslationSyncsExistingSiblings(): void
    {
        $article = ArticleFactory::createOne();
        $fr = ContentTranslationFactory::createOne(['article' => $article, 'locale' => 'fr']);

        $this->assertNull($fr->getReferenceSyncedAt());

        $bus = static::getContainer()->get(MessageBusInterface::class);
        $bus->dispatch(new CreateContentTranslationCommand(
            pageId: null,
            articleId: $article->getId(),
            preTitle: '',
            title: 'English Title',
            subTitle: '',
            slug: 'english-title-' . uniqid(),
            content: ['blocks' => []],
            description: '',
            locale: 'en',
            userIdentifier: 'test@example.com',
        ));

        $contentTransRepo = static::getContainer()->get(ContentTranslationRepository::class);
        $frReloaded = $contentTransRepo->find($fr->getId());

        $this->assertNotNull($frReloaded->getReferenceSyncedAt(), 'fr should have referenceSyncedAt set after en was created');
    }

    /**
     * Article created with en (reference) first, then fr is created.
     * The new fr translation should be stamped with en.updatedAt so it
     * starts in sync with the reference instead of a NULL snapshot.
     */
    public function testCreatingTranslationAgainstExistingReferenceStampsItAsSynced(): void
    {
        $article = ArticleFactory::createOne();
        $en = ContentTranslationFactory::createOne(['article' => $article, 'locale' => 'en']);

        $bus = static::getContainer()->get(MessageBusInterface::class);
        $bus->dispatch(new CreateContentTranslationCommand(
            pageId: null,
            articleId: $article->getId(),
            preTitle: '',
            title: 'Titre français',
            subTitle: '',
            slug: 'titre-francais-' . uniqid(),
            content: ['blocks' => []],
            description: '',
            locale: 'fr',
            userIdentifier: 'test@example.com',
        ));

        $contentTransRepo = static::getContainer()->get(ContentTranslationRepository::class);
        $fr = $contentTransRepo->findOneBy(['article' => $article->getId(), 'locale' => 'fr']);

        $this->assertNotNull($fr, 'fr translation should have been created');
        $this->assertEquals(
            $en->getUpdatedAt(),
            $fr->getReferenceSyncedAt(),
            'fr should be stamped with en.updatedAt at creation, not left NULL',
        );
    }

    /**
     * Creating a translation when no reference exists yet leaves
     * referenceSyncedAt NULL - there is nothing to be in sync with.
     */
    public function testCreatingTranslationWithoutReferenceLeavesSnapshotNull(): void
    {
        $article = ArticleFactory::createOne();

        $bus = static::getContainer()->get(MessageBusInterface::class);
        $bus->dispatch(new CreateContentTranslationCommand(
            pageId: null,
            articleId: $article->getId(),
            preTitle: '',
            title: 'Titre français',
            subTitle: '',
            slug: 'titre-francais-' . uniqid(),
            content: ['blocks' => []],
            description: '',
            locale: 'fr',
            userIdentifier: 'test@example.com',
        ));

        $contentTransRepo = static::getContainer()->get(ContentTranslationRepository::class);
        $fr = $contentTransRepo->findOneBy(['article' => $article->getId(), 'locale' => 'fr']);

        $this->assertNotNull($fr, 'fr translation should have been created');
        $this->assertNull($fr->getReferenceSyncedAt(), 'no reference exists, so snapshot stays NULL');
    }

    /**
     * Creating a non-reference translation (e.g. de) should NOT affect
     * existing siblings' referenceSyncedAt.
     */
    public function testCreatingNonReferenceTranslationDoesNotSyncSiblings(): void
    {
        $article = ArticleFactory::createOne();
        $fr = ContentTranslationFactory::createOne(['article' => $article, 'locale' => 'fr']);
        ContentTranslationFactory::createOne(['article' => $article, 'locale' => 'en']);

        $this->assertNull($fr->getReferenceSyncedAt());

        $bus = static::getContainer()->get(MessageBusInterface::class);
        $bus->dispatch(new CreateContentTranslationCommand(
            pageId: null,
            articleId: $article->getId(),
            preTitle: '',
            title: 'German Title',
            subTitle: '',
            slug: 'german-title-' . uniqid(),
            content: ['blocks' => []],
            description: '',
            locale: 'de',
            userIdentifier: 'test@example.com',
        ));

        $contentTransRepo = static::getContainer()->get(ContentTranslationRepository::class);
        $frReloaded = $contentTransRepo->find($fr->getId());

        $this->assertNull($frReloaded->getReferenceSyncedAt(), 'fr should remain unchanged when de is created');
    }
}
