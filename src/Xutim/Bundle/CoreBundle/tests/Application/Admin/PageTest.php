<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Tests\Application\Admin;

use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Xutim\CoreBundle\Repository\ContentDraftRepository;
use Xutim\CoreBundle\Repository\ContentTranslationRepository;

class PageTest extends AdminApplicationTestCase
{
    public function testPageLifecycle(): void
    {
        $uniqueId = uniqid();
        $slug = 'test-page-' . $uniqueId;
        $title = 'Test Page ' . $uniqueId;

        $client = $this->createAuthenticatedClient();

        $client->request('GET', '/admin/en/page-list');
        $this->assertResponseIsSuccessful('Page list should be accessible');

        $crawler = $client->request('GET', '/admin/en/page/new');
        $this->assertResponseIsSuccessful('Page create form should be accessible');

        $form = $crawler->selectButton('article_translation_submit')->form();
        $form['page[layout]'] = 'standard';
        $form['page[preTitle]'] = 'Intro ' . $uniqueId;
        $form['page[title]'] = $title;
        $form['page[subTitle]'] = 'Sub ' . $uniqueId;
        $form['page[slug]'] = $slug;
        $form['page[content]'] = json_encode([], JSON_THROW_ON_ERROR);
        $form['page[description]'] = 'Description ' . $uniqueId;
        $form['page[locale]'] = 'en';
        $client->submit($form);
        $this->assertResponseRedirects(message: 'Creating page should redirect');

        /** @var ContentTranslationRepository $contentTransRepo */
        $contentTransRepo = static::getContainer()->get(ContentTranslationRepository::class);
        $translation = $contentTransRepo->findOneBy(['slug' => $slug, 'locale' => 'en']);
        $this->assertNotNull($translation, 'Translation should exist after page creation');

        $page = $translation->getPage();
        $pageId = $page->getId()->toRfc4122();
        $translationId = $translation->getId()->toRfc4122();

        $crawler = $client->request('GET', '/admin/en/page/edit/' . $pageId);
        $this->assertResponseIsSuccessful('Page edit form should be accessible');

        $form = $crawler->filter('form[name="content_translation"]')->form();
        $this->assertEquals($title, $form['content_translation[title]']->getValue(), 'Edit form should be pre-filled with created title');
        $this->assertEquals($slug, $form['content_translation[slug]']->getValue(), 'Edit form should be pre-filled with created slug');

        $editedTitle = 'Edited Page ' . $uniqueId;
        $editedSlug = 'edited-page-' . $uniqueId;
        $form['content_translation[preTitle]'] = 'Edited Intro';
        $form['content_translation[title]'] = $editedTitle;
        $form['content_translation[subTitle]'] = 'Edited Sub';
        $form['content_translation[slug]'] = $editedSlug;
        $form['content_translation[content]'] = json_encode([], JSON_THROW_ON_ERROR);
        $form['content_translation[description]'] = 'Edited Description';
        $client->submit($form);
        $this->assertResponseRedirects(message: 'Editing page should redirect back');

        $crawler = $client->followRedirect();
        $this->assertResponseIsSuccessful('Edit page should be accessible after edit');
        $form = $crawler->filter('form[name="content_translation"]')->form();
        $this->assertEquals($editedTitle, $form['content_translation[title]']->getValue(), 'Form should show updated title after edit');
        $this->assertEquals($editedSlug, $form['content_translation[slug]']->getValue(), 'Form should show updated slug after edit');

        // Publish the page translation
        $csrfToken = $this->extractCsrfToken($client, '/admin/en/page/edit/' . $pageId);
        $client->request('POST', '/admin/en/publication-status/edit/' . $translationId . '/published', [
            'form' => ['_token' => $csrfToken],
        ]);
        $this->assertResponseRedirects(message: 'Publishing should redirect');

        /** @var ContentTranslationRepository $contentTransRepo */
        $contentTransRepo = static::getContainer()->get(ContentTranslationRepository::class);
        $translation = $contentTransRepo->find($translationId);
        $this->assertTrue($translation->isPublished(), 'Translation should be published after status change');
    }

    public function testDraftMechanismForPublishedPage(): void
    {
        $uniqueId = uniqid();
        $slug = 'draft-page-' . $uniqueId;
        $title = 'Draft Page ' . $uniqueId;

        $client = $this->createAuthenticatedClient();

        // Create and publish a page
        $crawler = $client->request('GET', '/admin/en/page/new');
        $form = $crawler->selectButton('article_translation_submit')->form();
        $form['page[layout]'] = 'standard';
        $form['page[preTitle]'] = 'Intro';
        $form['page[title]'] = $title;
        $form['page[subTitle]'] = 'Sub';
        $form['page[slug]'] = $slug;
        $form['page[content]'] = json_encode([], JSON_THROW_ON_ERROR);
        $form['page[description]'] = 'Description';
        $form['page[locale]'] = 'en';
        $client->submit($form);

        /** @var ContentTranslationRepository $contentTransRepo */
        $contentTransRepo = static::getContainer()->get(ContentTranslationRepository::class);
        $translation = $contentTransRepo->findOneBy(['slug' => $slug, 'locale' => 'en']);
        $this->assertNotNull($translation, 'Translation should exist');

        $page = $translation->getPage();
        $pageId = $page->getId()->toRfc4122();
        $translationId = $translation->getId()->toRfc4122();

        // Publish
        $csrfToken = $this->extractCsrfToken($client, '/admin/en/page/edit/' . $pageId);
        $client->request('POST', '/admin/en/publication-status/edit/' . $translationId . '/published', [
            'form' => ['_token' => $csrfToken],
        ]);
        $this->assertResponseRedirects(message: 'Publishing should redirect');

        // Edit the published page — should save to draft
        $crawler = $client->request('GET', '/admin/en/page/edit/' . $pageId);
        $this->assertResponseIsSuccessful('Edit form should be accessible for published page');

        $form = $crawler->filter('form[name="content_translation"]')->form();
        $draftTitle = 'Drafted Page Title ' . $uniqueId;
        $form['content_translation[title]'] = $draftTitle;
        $form['content_translation[content]'] = json_encode([], JSON_THROW_ON_ERROR);
        $form['_save_action'] = 'draft';
        $client->submit($form);
        $this->assertResponseRedirects(message: 'Editing published page should redirect');

        // Verify draft was created and form shows draft content
        $crawler = $client->followRedirect();
        $this->assertResponseIsSuccessful('Edit page should be accessible after editing published page');
        $form = $crawler->filter('form[name="content_translation"]')->form();
        $this->assertEquals($draftTitle, $form['content_translation[title]']->getValue(), 'Form should show draft title');

        // Re-obtain repos from current container after kernel reboot
        /** @var ContentTranslationRepository $contentTransRepo */
        $contentTransRepo = static::getContainer()->get(ContentTranslationRepository::class);

        // Verify live translation is unchanged
        $translation = $contentTransRepo->find($translationId);
        $this->assertEquals($title, $translation->getTitle(), 'Live translation title should remain unchanged after draft save');

        // Verify draft banner is shown
        $this->assertSelectorExists('.alert-warning', 'Draft banner should be displayed');

        // Extract CSRF token from the draft banner on the current page
        $csrfNode = $crawler->filter('[data-dialog-modal-csrf-token-value]')->first();
        $this->assertGreaterThan(0, $csrfNode->count(), 'CSRF token should be present in draft banner');
        $csrfToken = $csrfNode->attr('data-dialog-modal-csrf-token-value');

        // Find the draft
        /** @var ContentDraftRepository $draftRepo */
        $draftRepo = static::getContainer()->get(ContentDraftRepository::class);
        $draft = $draftRepo->findDraft($translation);
        $this->assertNotNull($draft, 'Draft should exist after editing published page');
        $this->assertEquals($draftTitle, $draft->getTitle(), 'Draft should contain the edited title');

        // Publish the draft
        $draftId = $draft->getId()->toRfc4122();
        $client->request('POST', '/admin/en/content-draft/' . $draftId . '/publish', [
            'form' => ['_token' => $csrfToken],
        ], [], ['HTTP_REFERER' => '/admin/en/page/edit/' . $pageId]);
        $this->assertResponseRedirects(message: 'Publishing draft should redirect');

        // Re-obtain repos from the new container after kernel reboot
        /** @var ContentTranslationRepository $contentTransRepo */
        $contentTransRepo = static::getContainer()->get(ContentTranslationRepository::class);
        /** @var ContentDraftRepository $draftRepo */
        $draftRepo = static::getContainer()->get(ContentDraftRepository::class);

        // Verify live translation now has the draft content
        $translation = $contentTransRepo->find($translationId);
        $this->assertEquals($draftTitle, $translation->getTitle(), 'Live translation should have draft title after publish');

        // Verify draft is removed
        $draft = $draftRepo->findDraft($translation);
        $this->assertNull($draft, 'Draft should be removed after publishing');
    }

    public function testDraftDiscardForPublishedPage(): void
    {
        $uniqueId = uniqid();
        $slug = 'discard-page-' . $uniqueId;
        $title = 'Discard Page ' . $uniqueId;

        $client = $this->createAuthenticatedClient();

        // Create and publish a page
        $crawler = $client->request('GET', '/admin/en/page/new');
        $form = $crawler->selectButton('article_translation_submit')->form();
        $form['page[layout]'] = 'standard';
        $form['page[preTitle]'] = 'Intro';
        $form['page[title]'] = $title;
        $form['page[subTitle]'] = 'Sub';
        $form['page[slug]'] = $slug;
        $form['page[content]'] = json_encode([], JSON_THROW_ON_ERROR);
        $form['page[description]'] = 'Description';
        $form['page[locale]'] = 'en';
        $client->submit($form);

        /** @var ContentTranslationRepository $contentTransRepo */
        $contentTransRepo = static::getContainer()->get(ContentTranslationRepository::class);
        $translation = $contentTransRepo->findOneBy(['slug' => $slug, 'locale' => 'en']);
        $page = $translation->getPage();
        $pageId = $page->getId()->toRfc4122();
        $translationId = $translation->getId()->toRfc4122();

        // Publish
        $csrfToken = $this->extractCsrfToken($client, '/admin/en/page/edit/' . $pageId);
        $client->request('POST', '/admin/en/publication-status/edit/' . $translationId . '/published', [
            'form' => ['_token' => $csrfToken],
        ]);

        // Edit the published page to create a draft
        $crawler = $client->request('GET', '/admin/en/page/edit/' . $pageId);
        $form = $crawler->filter('form[name="content_translation"]')->form();
        $form['content_translation[title]'] = 'Should Be Discarded';
        $form['content_translation[content]'] = json_encode([], JSON_THROW_ON_ERROR);
        $form['_save_action'] = 'draft';
        $client->submit($form);
        $crawler = $client->followRedirect();

        // Extract CSRF token from the draft banner
        $csrfNode = $crawler->filter('[data-dialog-modal-csrf-token-value]')->first();
        $this->assertGreaterThan(0, $csrfNode->count(), 'CSRF token should be present in draft banner');
        $csrfToken = $csrfNode->attr('data-dialog-modal-csrf-token-value');

        // Re-obtain repos from current container
        /** @var ContentDraftRepository $draftRepo */
        $draftRepo = static::getContainer()->get(ContentDraftRepository::class);
        /** @var ContentTranslationRepository $contentTransRepo */
        $contentTransRepo = static::getContainer()->get(ContentTranslationRepository::class);
        $user = $this->getTestUser();
        $translation = $contentTransRepo->find($translationId);
        $draft = $draftRepo->findUserDraft($translation, $user);
        $this->assertNotNull($draft, 'Draft should exist before discard');
        $draftId = $draft->getId()->toRfc4122();

        // Discard the draft
        $client->request('POST', '/admin/en/content-draft/' . $draftId . '/discard', [
            'form' => ['_token' => $csrfToken],
        ], [], ['HTTP_REFERER' => '/admin/en/page/edit/' . $pageId]);
        $this->assertResponseRedirects(message: 'Discarding draft should redirect');

        // Re-obtain repos from the new container after kernel reboot
        /** @var ContentTranslationRepository $contentTransRepo */
        $contentTransRepo = static::getContainer()->get(ContentTranslationRepository::class);
        /** @var ContentDraftRepository $draftRepo */
        $draftRepo = static::getContainer()->get(ContentDraftRepository::class);

        // Verify live translation is unchanged
        $translation = $contentTransRepo->find($translationId);
        $this->assertEquals($title, $translation->getTitle(), 'Live translation should remain unchanged after discard');

        // Verify draft is removed
        $draft = $draftRepo->findDraft($translation);
        $this->assertNull($draft, 'Draft should be removed after discarding');

        // Verify edit form shows live content again (no draft)
        $crawler = $client->request('GET', '/admin/en/page/edit/' . $pageId);
        $form = $crawler->filter('form[name="content_translation"]')->form();
        $this->assertEquals($title, $form['content_translation[title]']->getValue(), 'Form should show live title after draft discard');
    }

    public function testPageListIsAccessible(): void
    {
        $client = $this->createAuthenticatedClient();
        $client->request('GET', '/admin/en/page-list');
        $this->assertResponseIsSuccessful('Page list page should return 200');
    }

    public function testPageCreateFormIsAccessible(): void
    {
        $client = $this->createAuthenticatedClient();
        $client->request('GET', '/admin/en/page/new');
        $this->assertResponseIsSuccessful('Page create form should return 200');
    }

    private function extractCsrfToken(KernelBrowser $client, string $url): string
    {
        $crawler = $client->request('GET', $url);
        $node = $crawler->filter('[data-dialog-modal-csrf-token-value]')->first();
        $this->assertGreaterThan(0, $node->count(), 'CSRF token should be present on the page');

        return $node->attr('data-dialog-modal-csrf-token-value');
    }
}
