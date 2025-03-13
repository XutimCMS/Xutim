<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Tests\Application\Admin;

use Symfony\Component\DomCrawler\Crawler;

class BlockTest extends AdminApplicationTestCase
{
    public function testItShowsEmptyList(): void
    {
        $client = $this->createAuthenticatedClient();
        $client->request('GET', '/admin/block?searchTerm=');
        $this->assertResponseIsSuccessful();

        $client->clickLink('New block');
        $this->assertResponseIsSuccessful();

        $client->submitForm('submit', [
            'block[code]' => 'carousel',
            'block[name]' => 'Carousel',
            'block[description]' => 'This is a carousel block.'
        ]);

        $this->assertResponseRedirects('/admin/block?searchTerm=');
        $crawler = $client->followRedirect();
        $this->assertResponseIsSuccessful();
        $this->assertAnySelectorTextContains('td', 'Carousel');

        $link = $crawler->filter('tbody')->filter('a')->link();
        $crawler = $client->click($link);
        $this->assertResponseIsSuccessful();

        $form = $crawler->filter('form[name="block"]')->form();
        $this->assertEquals('carousel', $form['block[code]']->getValue());
        $this->assertEquals('Carousel', $form['block[name]']->getValue());
        $this->assertEquals('This is a carousel block.', $form['block[description]']->getValue());

        $client->clickLink('Edit');
        $this->assertResponseIsSuccessful();
        $form = $crawler->filter('form[name="block"]')->form();
        $this->assertEquals('carousel', $form['block[code]']->getValue());
        $this->assertEquals('Carousel', $form['block[name]']->getValue());
        $this->assertEquals('This is a carousel block.', $form['block[description]']->getValue());

        $client->submitForm('submit', [
            'block[code]' => 'carousel-1',
            'block[name]' => 'Carousel 1',
            'block[description]' => 'This is a carousel block. 1'
        ]);
        $crawler = $client->followRedirect();
        $this->assertResponseIsSuccessful();

        $this->assertAnySelectorTextContains('td', 'Carousel 1');
        $link = $crawler->filter('tbody')->filter('a')->link();
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
