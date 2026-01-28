<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Tests\Application\Admin;

use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Xutim\CoreBundle\Repository\ContentTranslationRepository;

class EditingPresenceTest extends AdminApplicationTestCase
{
    public function testHeartbeatAndStopEditing(): void
    {
        $uniqueId = uniqid();
        $slug = 'presence-test-' . $uniqueId;

        $client = $this->createAuthenticatedClient();

        // Create an article
        $crawler = $client->request('GET', '/admin/en/article/new');
        $form = $crawler->selectButton('article_translation_submit')->form();
        $form['article[layout]'] = 'standard';
        $form['article[preTitle]'] = 'Intro';
        $form['article[title]'] = 'Presence Test ' . $uniqueId;
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
        $this->assertNotNull($translation, 'Translation should exist');

        $article = $translation->getArticle();
        $articleId = $article->getId()->toRfc4122();
        $translationId = $translation->getId()->toRfc4122();

        // Publish the article
        $csrfToken = $this->extractCsrfToken($client, '/admin/en/article/edit/' . $articleId);
        $client->request('POST', '/admin/en/publication-status/edit/' . $translationId . '/published', [
            'form' => ['_token' => $csrfToken],
        ]);
        $this->assertResponseRedirects(message: 'Publishing should redirect');
        $client->followRedirect();

        // POST heartbeat
        $client->request('POST', '/admin/en/content-translation/' . $translationId . '/heartbeat', [], [], [
            'HTTP_X-Requested-With' => 'XMLHttpRequest',
        ]);

        $this->assertResponseIsSuccessful('Heartbeat should return 200');

        $response = json_decode($client->getResponse()->getContent(), true, 512, JSON_THROW_ON_ERROR);
        $this->assertFalse($response['isOtherUserEditing'], 'No other user should be editing');
        $this->assertNotNull($response['heartbeatAt'], 'Heartbeat timestamp should be set');

        // Verify editing user is set on the translation
        /** @var ContentTranslationRepository $contentTransRepo */
        $contentTransRepo = static::getContainer()->get(ContentTranslationRepository::class);
        $translation = $contentTransRepo->find($translationId);
        $this->assertNotNull($translation->getEditingUser(), 'Editing user should be set after heartbeat');
        $this->assertNotNull($translation->getEditingHeartbeatAt(), 'Heartbeat timestamp should be set');

        // POST stop-editing
        $client->request('POST', '/admin/en/content-translation/' . $translationId . '/stop-editing');
        $this->assertResponseStatusCodeSame(204, 'Stop editing should return 204');

        // Verify editing user is cleared
        /** @var ContentTranslationRepository $contentTransRepo */
        $contentTransRepo = static::getContainer()->get(ContentTranslationRepository::class);
        $translation = $contentTransRepo->find($translationId);
        $this->assertNull($translation->getEditingUser(), 'Editing user should be cleared after stop-editing');
        $this->assertNull($translation->getEditingHeartbeatAt(), 'Heartbeat timestamp should be cleared');
    }

    private function extractCsrfToken(KernelBrowser $client, string $url): string
    {
        $crawler = $client->request('GET', $url);
        $node = $crawler->filter('[data-dialog-modal-csrf-token-value]')->first();
        $this->assertGreaterThan(0, $node->count(), 'CSRF token should be present on the page');

        return $node->attr('data-dialog-modal-csrf-token-value');
    }
}
