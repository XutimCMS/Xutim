<?php

declare(strict_types=1);

namespace Xutim\MediaBundle\Tests\Application\Admin;

use Xutim\MediaBundle\Tests\Application\MediaApplicationTestCase;

class FocalPointTest extends MediaApplicationTestCase
{
    public function testEditFocalPointPageLoads(): void
    {
        $client = $this->createAuthenticatedClient();
        $media = $this->createMediaInDb();

        $client->request('GET', '/media/' . $media->id()->toRfc4122() . '/focal-point/edit');
        $this->assertResponseIsSuccessful();
    }

    public function testEditFocalPointRejectsNonImage(): void
    {
        $client = $this->createAuthenticatedClient();
        $media = $this->createNonImageMediaInDb();

        $client->request('GET', '/media/' . $media->id()->toRfc4122() . '/focal-point/edit');
        $this->assertResponseStatusCodeSame(404);
    }

    public function testUpdateFocalPoint(): void
    {
        $client = $this->createAuthenticatedClient();
        $media = $this->createMediaInDb();

        $client->request(
            'POST',
            '/media/' . $media->id()->toRfc4122() . '/focal-point',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                'focalX' => 0.5,
                'focalY' => 0.3,
            ], JSON_THROW_ON_ERROR),
        );

        $this->assertResponseIsSuccessful();

        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertTrue($data['success']);
        $this->assertSame(0.5, $data['focalX']);
        $this->assertSame(0.3, $data['focalY']);
    }
}
