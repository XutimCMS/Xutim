<?php

declare(strict_types=1);

namespace Xutim\MediaBundle\Tests\Application\Admin;

use Xutim\MediaBundle\Infra\Storage\LocalStorageAdapter;
use Xutim\MediaBundle\Tests\Application\MediaApplicationTestCase;

class ShowMediaTranslationTest extends MediaApplicationTestCase
{
    public function testShowTranslationReturnsFile(): void
    {
        $client = $this->createAuthenticatedClient();

        /** @var LocalStorageAdapter $storage */
        $storage = static::getContainer()->get(LocalStorageAdapter::class);
        $filePath = 'test/show-' . uniqid() . '.txt';
        $storage->write($filePath, 'test file content');

        $media = $this->createMediaInDb(
            originalPath: $filePath,
            mime: 'text/plain',
            width: 0,
            height: 0,
        );

        $client->request('GET', '/media/show-translation/' . $media->id()->toRfc4122());
        $this->assertResponseIsSuccessful();
    }
}
