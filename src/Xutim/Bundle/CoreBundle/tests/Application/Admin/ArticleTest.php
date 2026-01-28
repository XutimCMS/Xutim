<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Tests\Application\Admin;

use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Xutim\CoreBundle\Domain\Event\ContentDraft\ContentDraftCreatedEvent;
use Xutim\CoreBundle\Domain\Event\ContentDraft\ContentDraftDiscardedEvent;
use Xutim\CoreBundle\Domain\Event\ContentDraft\ContentDraftUpdatedEvent;
use Xutim\CoreBundle\Domain\Event\ContentTranslation\ContentTranslationUpdatedEvent;
use Xutim\CoreBundle\Repository\ContentDraftRepository;
use Xutim\CoreBundle\Repository\ContentTranslationRepository;
use Xutim\CoreBundle\Repository\LogEventRepository;

class ArticleTest extends AdminApplicationTestCase
{
    public function testArticleLifecycle(): void
    {
        $uniqueId = uniqid();
        $slug = 'test-article-' . $uniqueId;
        $title = 'Test Article ' . $uniqueId;

        $client = $this->createAuthenticatedClient();

        $client->request('GET', '/admin/en/article');
        $this->assertResponseIsSuccessful('Article list should be accessible');

        $crawler = $client->request('GET', '/admin/en/article/new');
        $this->assertResponseIsSuccessful('Article create form should be accessible');

        $form = $crawler->selectButton('article_translation_submit')->form();
        $form['article[layout]'] = 'standard';
        $form['article[preTitle]'] = 'Intro ' . $uniqueId;
        $form['article[title]'] = $title;
        $form['article[subTitle]'] = 'Sub ' . $uniqueId;
        $form['article[slug]'] = $slug;
        $form['article[content]'] = json_encode([], JSON_THROW_ON_ERROR);
        $form['article[description]'] = 'Description ' . $uniqueId;
        $form['article[locale]'] = 'en';
        $client->submit($form);
        $this->assertResponseRedirects(message: 'Creating article should redirect to show page');

        $crawler = $client->followRedirect();
        $this->assertResponseIsSuccessful('Article show page should be accessible after creation');

        /** @var ContentTranslationRepository $contentTransRepo */
        $contentTransRepo = static::getContainer()->get(ContentTranslationRepository::class);
        $translation = $contentTransRepo->findOneBy(['slug' => $slug, 'locale' => 'en']);
        $this->assertNotNull($translation, 'Translation should exist after article creation');

        $article = $translation->getArticle();
        $articleId = $article->getId()->toRfc4122();
        $translationId = $translation->getId()->toRfc4122();

        $crawler = $client->request('GET', '/admin/en/article/edit/' . $articleId);
        $this->assertResponseIsSuccessful('Article edit form should be accessible');

        $form = $crawler->filter('form[name="content_translation"]')->form();
        $this->assertEquals($title, $form['content_translation[title]']->getValue(), 'Edit form should be pre-filled with created title');
        $this->assertEquals($slug, $form['content_translation[slug]']->getValue(), 'Edit form should be pre-filled with created slug');

        $editedTitle = 'Edited Article ' . $uniqueId;
        $editedSlug = 'edited-article-' . $uniqueId;
        $form['content_translation[preTitle]'] = 'Edited Intro';
        $form['content_translation[title]'] = $editedTitle;
        $form['content_translation[subTitle]'] = 'Edited Sub';
        $form['content_translation[slug]'] = $editedSlug;
        $form['content_translation[content]'] = json_encode([], JSON_THROW_ON_ERROR);
        $form['content_translation[description]'] = 'Edited Description';
        $client->submit($form);
        $this->assertResponseRedirects(message: 'Editing article should redirect back');

        $crawler = $client->followRedirect();
        $this->assertResponseIsSuccessful('Edit page should be accessible after edit');
        $form = $crawler->filter('form[name="content_translation"]')->form();
        $this->assertEquals($editedTitle, $form['content_translation[title]']->getValue(), 'Form should show updated title after edit');
        $this->assertEquals($editedSlug, $form['content_translation[slug]']->getValue(), 'Form should show updated slug after edit');

        $csrfToken = $this->extractCsrfToken($client, '/admin/en/article/' . $articleId);
        $client->request('POST', '/admin/en/publication-status/edit/' . $translationId . '/published', [
            'form' => ['_token' => $csrfToken],
        ]);
        $this->assertResponseRedirects(message: 'Publishing should redirect');

        /** @var ContentTranslationRepository $contentTransRepo */
        $contentTransRepo = static::getContainer()->get(ContentTranslationRepository::class);
        $translation = $contentTransRepo->find($translationId);
        $this->assertTrue($translation->isPublished(), 'Translation should be published after status change');
    }

    public function testDraftMechanismForPublishedArticle(): void
    {
        $uniqueId = uniqid();
        $slug = 'draft-article-' . $uniqueId;
        $title = 'Draft Article ' . $uniqueId;

        $client = $this->createAuthenticatedClient();

        $crawler = $client->request('GET', '/admin/en/article/new');
        $form = $crawler->selectButton('article_translation_submit')->form();
        $form['article[layout]'] = 'standard';
        $form['article[preTitle]'] = 'Intro';
        $form['article[title]'] = $title;
        $form['article[subTitle]'] = 'Sub';
        $form['article[slug]'] = $slug;
        $form['article[content]'] = json_encode([], JSON_THROW_ON_ERROR);
        $form['article[description]'] = 'Description';
        $form['article[locale]'] = 'en';
        $client->submit($form);
        $client->followRedirect();

        /** @var ContentTranslationRepository $contentTransRepo */
        $contentTransRepo = static::getContainer()->get(ContentTranslationRepository::class);
        $translation = $contentTransRepo->findOneBy(['slug' => $slug, 'locale' => 'en']);
        $this->assertNotNull($translation);

        $article = $translation->getArticle();
        $articleId = $article->getId()->toRfc4122();
        $translationId = $translation->getId()->toRfc4122();

        $csrfToken = $this->extractCsrfToken($client, '/admin/en/article/' . $articleId);
        $client->request('POST', '/admin/en/publication-status/edit/' . $translationId . '/published', [
            'form' => ['_token' => $csrfToken],
        ]);
        $this->assertResponseRedirects();

        $crawler = $client->request('GET', '/admin/en/article/edit/' . $articleId);
        $this->assertResponseIsSuccessful();

        $form = $crawler->filter('form[name="content_translation"]')->form();
        $draftTitle = 'Drafted Title ' . $uniqueId;
        $form['content_translation[title]'] = $draftTitle;
        $form['content_translation[content]'] = json_encode([], JSON_THROW_ON_ERROR);
        $client->submit($form);
        $this->assertResponseRedirects();

        $crawler = $client->followRedirect();
        $this->assertResponseIsSuccessful();
        $form = $crawler->filter('form[name="content_translation"]')->form();
        $this->assertEquals($draftTitle, $form['content_translation[title]']->getValue(), 'Form should show draft title');

        /** @var ContentTranslationRepository $contentTransRepo */
        $contentTransRepo = static::getContainer()->get(ContentTranslationRepository::class);
        $translation = $contentTransRepo->find($translationId);
        $this->assertEquals($title, $translation->getTitle(), 'Live translation title should remain unchanged after draft save');

        /** @var LogEventRepository $logEventRepo */
        $logEventRepo = static::getContainer()->get(LogEventRepository::class);
        $events = $logEventRepo->findByTranslation($translation);
        $draftCreatedEvents = array_filter($events, static fn ($e) => $e->getEvent() instanceof ContentDraftCreatedEvent);
        $this->assertCount(1, $draftCreatedEvents, 'ContentDraftCreatedEvent should be persisted after first draft save');

        $form['content_translation[title]'] = 'Updated Draft ' . $uniqueId;
        $form['content_translation[content]'] = json_encode([], JSON_THROW_ON_ERROR);
        $client->submit($form);
        $crawler = $client->followRedirect();

        /** @var ContentTranslationRepository $contentTransRepo */
        $contentTransRepo = static::getContainer()->get(ContentTranslationRepository::class);
        $translation = $contentTransRepo->find($translationId);

        /** @var LogEventRepository $logEventRepo */
        $logEventRepo = static::getContainer()->get(LogEventRepository::class);
        $events = $logEventRepo->findByTranslation($translation);
        $draftUpdatedEvents = array_filter($events, static fn ($e) => $e->getEvent() instanceof ContentDraftUpdatedEvent);
        $this->assertCount(1, $draftUpdatedEvents, 'ContentDraftUpdatedEvent should be persisted after editing existing draft');

        $this->assertSelectorExists('.alert-warning', 'Draft banner should be displayed');

        $csrfNode = $crawler->filter('[data-dialog-modal-csrf-token-value]')->first();
        $this->assertGreaterThan(0, $csrfNode->count(), 'CSRF token should be present in draft banner');
        $csrfToken = $csrfNode->attr('data-dialog-modal-csrf-token-value');

        /** @var ContentDraftRepository $draftRepo */
        $draftRepo = static::getContainer()->get(ContentDraftRepository::class);
        $draft = $draftRepo->findDraft($translation);
        $this->assertNotNull($draft);

        $draftId = $draft->getId()->toRfc4122();
        $client->request('POST', '/admin/en/content-draft/' . $draftId . '/publish', [
            'form' => ['_token' => $csrfToken],
        ], [], ['HTTP_REFERER' => '/admin/en/article/edit/' . $articleId]);
        $this->assertResponseRedirects();

        /** @var ContentTranslationRepository $contentTransRepo */
        $contentTransRepo = static::getContainer()->get(ContentTranslationRepository::class);
        /** @var ContentDraftRepository $draftRepo */
        $draftRepo = static::getContainer()->get(ContentDraftRepository::class);

        $translation = $contentTransRepo->find($translationId);
        $this->assertEquals('Updated Draft ' . $uniqueId, $translation->getTitle(), 'Live translation should have draft title after publish');

        $draft = $draftRepo->findDraft($translation);
        $this->assertNull($draft, 'Draft should be removed after publishing');

        /** @var LogEventRepository $logEventRepo */
        $logEventRepo = static::getContainer()->get(LogEventRepository::class);
        $events = $logEventRepo->findByTranslation($translation);
        $publishEvents = array_filter($events, static fn ($e) => $e->getEvent() instanceof ContentTranslationUpdatedEvent);
        $this->assertNotEmpty($publishEvents, 'ContentTranslationUpdatedEvent should be persisted after draft publish');
    }

    public function testDraftDiscardForPublishedArticle(): void
    {
        $uniqueId = uniqid();
        $slug = 'discard-article-' . $uniqueId;
        $title = 'Discard Article ' . $uniqueId;

        $client = $this->createAuthenticatedClient();

        $crawler = $client->request('GET', '/admin/en/article/new');
        $form = $crawler->selectButton('article_translation_submit')->form();
        $form['article[layout]'] = 'standard';
        $form['article[preTitle]'] = 'Intro';
        $form['article[title]'] = $title;
        $form['article[subTitle]'] = 'Sub';
        $form['article[slug]'] = $slug;
        $form['article[content]'] = json_encode([], JSON_THROW_ON_ERROR);
        $form['article[description]'] = 'Description';
        $form['article[locale]'] = 'en';
        $client->submit($form);
        $client->followRedirect();

        /** @var ContentTranslationRepository $contentTransRepo */
        $contentTransRepo = static::getContainer()->get(ContentTranslationRepository::class);
        $translation = $contentTransRepo->findOneBy(['slug' => $slug, 'locale' => 'en']);
        $article = $translation->getArticle();
        $articleId = $article->getId()->toRfc4122();
        $translationId = $translation->getId()->toRfc4122();

        $csrfToken = $this->extractCsrfToken($client, '/admin/en/article/' . $articleId);
        $client->request('POST', '/admin/en/publication-status/edit/' . $translationId . '/published', [
            'form' => ['_token' => $csrfToken],
        ]);

        $crawler = $client->request('GET', '/admin/en/article/edit/' . $articleId);
        $form = $crawler->filter('form[name="content_translation"]')->form();
        $form['content_translation[title]'] = 'Should Be Discarded';
        $form['content_translation[content]'] = json_encode([], JSON_THROW_ON_ERROR);
        $client->submit($form);
        $crawler = $client->followRedirect();

        $csrfNode = $crawler->filter('[data-dialog-modal-csrf-token-value]')->first();
        $this->assertGreaterThan(0, $csrfNode->count(), 'CSRF token should be present in draft banner');
        $csrfToken = $csrfNode->attr('data-dialog-modal-csrf-token-value');

        /** @var ContentDraftRepository $draftRepo */
        $draftRepo = static::getContainer()->get(ContentDraftRepository::class);
        /** @var ContentTranslationRepository $contentTransRepo */
        $contentTransRepo = static::getContainer()->get(ContentTranslationRepository::class);
        $user = $this->getTestUser();
        $translation = $contentTransRepo->find($translationId);
        $draft = $draftRepo->findUserDraft($translation, $user);
        $this->assertNotNull($draft, 'Draft should exist before discard');
        $draftId = $draft->getId()->toRfc4122();

        /** @var LogEventRepository $logEventRepo */
        $logEventRepo = static::getContainer()->get(LogEventRepository::class);
        $events = $logEventRepo->findByTranslation($translation);
        $draftCreatedEvents = array_filter($events, static fn ($e) => $e->getEvent() instanceof ContentDraftCreatedEvent);
        $this->assertCount(1, $draftCreatedEvents, 'ContentDraftCreatedEvent should be persisted after draft creation');

        $client->request('POST', '/admin/en/content-draft/' . $draftId . '/discard', [
            'form' => ['_token' => $csrfToken],
        ], [], ['HTTP_REFERER' => '/admin/en/article/edit/' . $articleId]);
        $this->assertResponseRedirects();

        /** @var ContentTranslationRepository $contentTransRepo */
        $contentTransRepo = static::getContainer()->get(ContentTranslationRepository::class);
        /** @var ContentDraftRepository $draftRepo */
        $draftRepo = static::getContainer()->get(ContentDraftRepository::class);

        $translation = $contentTransRepo->find($translationId);
        $this->assertEquals($title, $translation->getTitle(), 'Live translation should remain unchanged after discard');

        $draft = $draftRepo->findDraft($translation);
        $this->assertNull($draft, 'Draft should be removed after discarding');

        /** @var LogEventRepository $logEventRepo */
        $logEventRepo = static::getContainer()->get(LogEventRepository::class);
        $events = $logEventRepo->findByTranslation($translation);
        $discardedEvents = array_filter($events, static fn ($e) => $e->getEvent() instanceof ContentDraftDiscardedEvent);
        $this->assertCount(1, $discardedEvents, 'ContentDraftDiscardedEvent should be persisted after draft discard');

        $crawler = $client->request('GET', '/admin/en/article/edit/' . $articleId);
        $form = $crawler->filter('form[name="content_translation"]')->form();
        $this->assertEquals($title, $form['content_translation[title]']->getValue(), 'Form should show live title after draft discard');
    }

    public function testArticleListIsAccessible(): void
    {
        $client = $this->createAuthenticatedClient();
        $client->request('GET', '/admin/en/article');
        $this->assertResponseIsSuccessful('Article list page should return 200');
    }

    public function testArticleCreateFormIsAccessible(): void
    {
        $client = $this->createAuthenticatedClient();
        $client->request('GET', '/admin/en/article/new');
        $this->assertResponseIsSuccessful('Article create form should return 200');
    }

    private function extractCsrfToken(KernelBrowser $client, string $url): string
    {
        $crawler = $client->request('GET', $url);
        $node = $crawler->filter('[data-dialog-modal-csrf-token-value]')->first();
        $this->assertGreaterThan(0, $node->count(), 'CSRF token should be present on the page');

        return $node->attr('data-dialog-modal-csrf-token-value');
    }
}
