<?php

declare(strict_types=1);

namespace Xutim\MediaBundle\Tests\Application\Admin;

use Xutim\MediaBundle\Infra\Storage\LocalStorageAdapter;
use Xutim\MediaBundle\Tests\Application\MediaApplicationTestCase;

class RegenerateVariantsTest extends MediaApplicationTestCase
{
    public function testRegenerateVariantsForImage(): void
    {
        $client = $this->createAuthenticatedClient();

        /** @var LocalStorageAdapter $storage */
        $storage = static::getContainer()->get(LocalStorageAdapter::class);
        $imagePath = 'test/regen-' . uniqid() . '.jpg';
        $this->createTestImageFile($storage, $imagePath);

        $media = $this->createMediaInDb(originalPath: $imagePath);

        $client->request(
            'POST',
            '/media/' . $media->id()->toRfc4122() . '/regenerate-variants',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
        );

        $this->assertResponseIsSuccessful();

        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertSame('processing', $data['status']);
    }

    public function testRegenerateVariantsRejectsNonImage(): void
    {
        $client = $this->createAuthenticatedClient();
        $media = $this->createNonImageMediaInDb();

        $client->request(
            'POST',
            '/media/' . $media->id()->toRfc4122() . '/regenerate-variants',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
        );

        $this->assertResponseStatusCodeSame(400);
    }

    private function createTestImageFile(LocalStorageAdapter $storage, string $path): void
    {
        $image = imagecreatetruecolor(100, 100);
        $color = imagecolorallocate($image, 255, 0, 0);
        imagefill($image, 0, 0, $color);

        ob_start();
        imagejpeg($image, null, 90);
        $contents = ob_get_clean();

        $storage->write($path, $contents);
    }
}
