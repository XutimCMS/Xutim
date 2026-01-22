<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Tests\Application\Admin;

use Symfony\Component\DomCrawler\Crawler;

class BlockTest extends AdminApplicationTestCase
{
    public function testItShowsEmptyList(): void
    {
        $uniqueId = uniqid();
        $blockCode = 'carousel-' . $uniqueId;
        $blockName = 'Carousel ' . $uniqueId;

        $client = $this->createAuthenticatedClient();
        $client->request('GET', '/admin/en/block?searchTerm=');
        $this->assertResponseIsSuccessful();

        $client->clickLink('New block');
        $this->assertResponseIsSuccessful();

        $client->submitForm('block_submit', [
            'block[code]' => $blockCode,
            'block[name]' => $blockName,
            'block[description]' => 'This is a carousel block.'
        ]);

        $this->assertResponseRedirects('/admin/en/block?searchTerm=');
        $crawler = $client->followRedirect();
        $this->assertResponseIsSuccessful();
        $this->assertAnySelectorTextContains('td', $blockName);

        // Find the link for our specific block by searching for the block name in the row
        $link = $crawler->filter('tbody tr')->reduce(function ($node) use ($blockName) {
            return str_contains($node->text(), $blockName);
        })->filter('a')->first()->link();
        $crawler = $client->click($link);
        $this->assertResponseIsSuccessful();

        $form = $crawler->filter('form[name="block"]')->form();
        $this->assertEquals($blockCode, $form['block[code]']->getValue());
        $this->assertEquals($blockName, $form['block[name]']->getValue());
        $this->assertEquals('This is a carousel block.', $form['block[description]']->getValue());

        $client->clickLink('Edit');
        $this->assertResponseIsSuccessful();
        $form = $crawler->filter('form[name="block"]')->form();
        $this->assertEquals($blockCode, $form['block[code]']->getValue());
        $this->assertEquals($blockName, $form['block[name]']->getValue());
        $this->assertEquals('This is a carousel block.', $form['block[description]']->getValue());

        $editedBlockCode = $blockCode . '-edited';
        $editedBlockName = $blockName . ' Edited';
        $client->submitForm('block_submit', [
            'block[code]' => $editedBlockCode,
            'block[name]' => $editedBlockName,
            'block[description]' => 'This is an edited carousel block.'
        ]);
        $crawler = $client->followRedirect();
        $this->assertResponseIsSuccessful();

        // Verify the block name in breadcrumb after edit
        $this->assertAnySelectorTextContains('li.breadcrumb-item', $editedBlockName);

        // Navigate to list page to verify updated name in table
        $crawler = $client->request('GET', '/admin/en/block?searchTerm=');
        $this->assertResponseIsSuccessful();
        $this->assertAnySelectorTextContains('td', $editedBlockName);
        $link = $crawler->filter('tbody tr')->reduce(function ($node) use ($editedBlockName) {
            return str_contains($node->text(), $editedBlockName);
        })->filter('a')->first()->link();
        $crawler = $client->click($link);
        $this->assertResponseIsSuccessful();

        $buttons = $crawler->filter('div.card-actions')->filter('button');
        $links = [];
        foreach ($buttons as $button) {
            $buttonCrawler = new Crawler($button);
            $links[] = $buttonCrawler->attr('data-modal-form-url-value');
        }

        // TODO: Finish with form submitting. Now we only test if the form displays.
        foreach ($links as $link) {
            $client->request('GET', $link);
            $this->assertResponseIsSuccessful();
        }
    }
}
