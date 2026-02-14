<?php

declare(strict_types=1);

namespace Xutim\MediaBundle\Tests\Application\Admin;

use Xutim\MediaBundle\Tests\Application\MediaApplicationTestCase;

class MoveMediaTest extends MediaApplicationTestCase
{
    public function testMoveFormLoads(): void
    {
        $client = $this->createAuthenticatedClient();
        $media = $this->createMediaInDb();

        $client->request('GET', '/media/' . $media->id()->toRfc4122() . '/move');
        $this->assertResponseIsSuccessful();
    }

    public function testMoveToFolder(): void
    {
        $client = $this->createAuthenticatedClient();
        $media = $this->createMediaInDb();
        $folder = $this->createFolderInDb('Target Folder');

        $client->request(
            'POST',
            '/media/' . $media->id()->toRfc4122() . '/move/' . $folder->id()->toRfc4122(),
        );

        $this->assertResponseRedirects();
    }
}
