<?php

declare(strict_types=1);

namespace Xutim\MediaBundle\Tests\Application\Admin;

use Xutim\MediaBundle\Tests\Application\MediaApplicationTestCase;

class CopyrightTest extends MediaApplicationTestCase
{
    public function testEditCopyrightFormLoads(): void
    {
        $client = $this->createAuthenticatedClient();
        $media = $this->createMediaInDb();

        $client->request('GET', '/media/' . $media->id()->toRfc4122() . '/copyright');
        $this->assertResponseIsSuccessful();
    }

    public function testEditCopyrightSubmit(): void
    {
        $client = $this->createAuthenticatedClient();
        $media = $this->createMediaInDb();
        $mediaId = $media->id()->toRfc4122();

        $crawler = $client->request('GET', '/media/' . $mediaId . '/copyright');
        $this->assertResponseIsSuccessful();

        $form = $crawler->filter('form[name="media_copyright"]')->form();
        $form['media_copyright[copyright]'] = 'Photo by John Doe';
        $client->submit($form);

        $this->assertResponseRedirects();
    }
}
