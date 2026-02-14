<?php

declare(strict_types=1);

namespace Xutim\MediaBundle\Tests\Application\Admin;

use Xutim\MediaBundle\Tests\Application\MediaApplicationTestCase;

class JsonListAllFilesTest extends MediaApplicationTestCase
{
    public function testJsonListReturnsFiles(): void
    {
        $client = $this->createAuthenticatedClient();

        $media = $this->createNonImageMediaInDb();
        $this->createTranslationInDb($media, 'en', 'Annual Report');

        $client->request('GET', '/json/file/all-list');
        $this->assertResponseIsSuccessful();

        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertIsArray($data);
        $this->assertArrayHasKey($media->id()->toRfc4122(), $data);
        $this->assertSame('Annual Report', $data[$media->id()->toRfc4122()]);
    }
}
