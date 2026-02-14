<?php

declare(strict_types=1);

namespace Xutim\MediaBundle\Tests\Application\Admin;

use Xutim\MediaBundle\Tests\Application\MediaApplicationTestCase;

class ListMediaTest extends MediaApplicationTestCase
{
    public function testListMediaPageLoads(): void
    {
        $client = $this->createAuthenticatedClient();

        $client->request('GET', '/media');
        $this->assertResponseIsSuccessful();
    }

    public function testListMediaWithFolder(): void
    {
        $client = $this->createAuthenticatedClient();
        $folder = $this->createFolderInDb('Gallery');

        $client->request('GET', '/media/' . $folder->id()->toRfc4122());
        $this->assertResponseIsSuccessful();
    }

    public function testListMediaWithSearch(): void
    {
        $client = $this->createAuthenticatedClient();
        $media = $this->createMediaInDb();
        $this->createTranslationInDb($media, 'en', 'Searchable Image');

        $client->request('GET', '/media?searchTerm=Searchable');
        $this->assertResponseIsSuccessful();
    }
}
