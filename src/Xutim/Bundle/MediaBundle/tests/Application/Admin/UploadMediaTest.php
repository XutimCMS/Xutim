<?php

declare(strict_types=1);

namespace Xutim\MediaBundle\Tests\Application\Admin;

use Xutim\MediaBundle\Tests\Application\MediaApplicationTestCase;

class UploadMediaTest extends MediaApplicationTestCase
{
    public function testUploadFormLoads(): void
    {
        $client = $this->createAuthenticatedClient();

        $client->request('GET', '/media/upload');
        $this->assertResponseIsSuccessful();
    }

    public function testUploadFormInFolderLoads(): void
    {
        $client = $this->createAuthenticatedClient();
        $folder = $this->createFolderInDb();

        $client->request('GET', '/media/upload/' . $folder->id()->toRfc4122());
        $this->assertResponseIsSuccessful();
    }
}
