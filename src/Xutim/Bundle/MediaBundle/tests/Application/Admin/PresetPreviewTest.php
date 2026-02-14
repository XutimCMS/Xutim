<?php

declare(strict_types=1);

namespace Xutim\MediaBundle\Tests\Application\Admin;

use Xutim\MediaBundle\Tests\Application\MediaApplicationTestCase;

class PresetPreviewTest extends MediaApplicationTestCase
{
    public function testPresetPreviewLoads(): void
    {
        $client = $this->createAuthenticatedClient();
        $media = $this->createMediaInDb();

        $client->request('GET', '/media/' . $media->id()->toRfc4122() . '/presets');
        $this->assertResponseIsSuccessful();
    }

    public function testPresetPreviewRejectsNonImage(): void
    {
        $client = $this->createAuthenticatedClient();
        $media = $this->createNonImageMediaInDb();

        $client->request('GET', '/media/' . $media->id()->toRfc4122() . '/presets');
        $this->assertResponseStatusCodeSame(404);
    }
}
