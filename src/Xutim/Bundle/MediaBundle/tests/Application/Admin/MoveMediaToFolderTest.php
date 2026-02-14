<?php

declare(strict_types=1);

namespace Xutim\MediaBundle\Tests\Application\Admin;

use Xutim\MediaBundle\Tests\Application\MediaApplicationTestCase;

class MoveMediaToFolderTest extends MediaApplicationTestCase
{
    public function testMoveToFolderJson(): void
    {
        $client = $this->createAuthenticatedClient();
        $media = $this->createMediaInDb();
        $folder = $this->createFolderInDb('Target');

        $client->request(
            'POST',
            '/media/move-to-folder',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                'fileId' => $media->id()->toRfc4122(),
                'targetFolderId' => $folder->id()->toRfc4122(),
            ], JSON_THROW_ON_ERROR),
        );

        $this->assertResponseIsSuccessful();

        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertTrue($data['success']);
    }
}
