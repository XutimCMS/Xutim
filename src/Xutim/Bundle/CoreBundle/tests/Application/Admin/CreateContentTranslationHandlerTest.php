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
