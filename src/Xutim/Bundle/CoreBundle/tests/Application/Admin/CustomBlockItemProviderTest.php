<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Tests\Application\Admin;

use Symfony\Component\DomCrawler\Crawler;

class CustomBlockItemProviderTest extends AdminApplicationTestCase
{
    public function testCustomEmbedUrlProviderFullLifecycle(): void
    {
        $client = $this->createAuthenticatedClient();
        $uniqueId = uniqid();

        // 1. Create block with 'embed' layout
        $client->request('GET', '/admin/en/block/new');
        $this->assertResponseIsSuccessful();

        $client->submitForm('block_submit', [
            'block[code]' => 'embed-test-' . $uniqueId,
            'block[name]' => 'Embed Test ' . $uniqueId,
            'block[description]' => 'Test block for embed url provider',
            'block[layout]' => 'embed',
        ]);
        $this->assertResponseRedirects();
        $crawler = $client->followRedirect();
        $this->assertResponseIsSuccessful();

        // Find the block in the list and navigate to show page
        $link = $crawler->filter('tbody tr')->reduce(function (Crawler $node) use ($uniqueId) {
            return str_contains($node->text(), 'Embed Test ' . $uniqueId);
        })->filter('a')->first()->link();
        $crawler = $client->click($link);
        $this->assertResponseIsSuccessful();

        // 2. Navigate to add-item form
        $blockId = $this->extractBlockId($crawler);
        $crawler = $client->request('GET', '/admin/en/block/add-item/' . $blockId);
        $this->assertResponseIsSuccessful();

        // Verify embedUrl field is present in the form
        $this->assertSelectorExists('input[id$="embedUrl"]');

        // 3. Submit with embedUrl value
        $client->submitForm('block_item_submit', [
            'block_item[embedUrl]' => 'https://youtube.com/watch?v=123',
        ]);
        $this->assertResponseRedirects();
        $crawler = $client->followRedirect();
        $this->assertResponseIsSuccessful();

        // 4. Find the item's edit link and navigate to edit form
        $editLink = $this->findEditItemLink($crawler);
        $crawler = $client->request('GET', $editLink);
        $this->assertResponseIsSuccessful();

        // Verify embedUrl is populated with the saved value
        $embedUrlInput = $crawler->filter('input[id$="embedUrl"]');
        $this->assertSame('https://youtube.com/watch?v=123', $embedUrlInput->attr('value'));

        // 5. Edit and re-submit with updated value
        $client->submitForm('block_item_submit', [
            'block_item[embedUrl]' => 'https://vimeo.com/456',
        ]);
        $this->assertResponseRedirects();
        $crawler = $client->followRedirect();
        $this->assertResponseIsSuccessful();

        // Verify updated value persists
        $editLink = $this->findEditItemLink($crawler);
        $crawler = $client->request('GET', $editLink);
        $this->assertResponseIsSuccessful();

        $embedUrlInput = $crawler->filter('input[id$="embedUrl"]');
        $this->assertSame('https://vimeo.com/456', $embedUrlInput->attr('value'));
    }

    private function extractBlockId(Crawler $crawler): string
    {
        $addItemHref = $crawler->filter('a[href*="add-item"]')->attr('href');
        \assert($addItemHref !== null);

        return basename($addItemHref);
    }

    private function findEditItemLink(Crawler $crawler): string
    {
        $href = $crawler->filter('a[href*="edit-item"]')->first()->attr('href');
        \assert($href !== null);

        return $href;
    }
}
