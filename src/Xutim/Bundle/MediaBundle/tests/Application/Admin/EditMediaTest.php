<?php

declare(strict_types=1);

namespace Xutim\MediaBundle\Tests\Application\Admin;

use Xutim\MediaBundle\Tests\Application\MediaApplicationTestCase;

class EditMediaTest extends MediaApplicationTestCase
{
    public function testEditFormLoads(): void
    {
        $client = $this->createAuthenticatedClient();
        $media = $this->createMediaInDb();

        $client->request('GET', '/media/' . $media->id()->toRfc4122() . '/edit');
        $this->assertResponseIsSuccessful();
    }

    public function testEditSubmit(): void
    {
        $client = $this->createAuthenticatedClient();
        $media = $this->createMediaInDb();
        $this->createTranslationInDb($media, 'en', 'Original Name', 'Original Alt');

        $crawler = $client->request('GET', '/media/' . $media->id()->toRfc4122() . '/edit');
        $this->assertResponseIsSuccessful();

        $form = $crawler->filter('form[name="media_translation"]')->form();
        $form['media_translation[name]'] = 'Updated Name';
        $form['media_translation[alt]'] = 'Updated Alt';
        $client->submit($form);

        $this->assertResponseRedirects();
    }
}
