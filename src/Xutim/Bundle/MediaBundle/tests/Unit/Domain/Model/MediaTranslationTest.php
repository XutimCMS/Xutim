<?php

declare(strict_types=1);

namespace Xutim\MediaBundle\Tests\Unit\Domain\Model;

use PHPUnit\Framework\TestCase;
use Xutim\MediaBundle\Domain\Model\MediaInterface;
use Xutim\MediaBundle\Domain\Model\MediaTranslation;

final class MediaTranslationTest extends TestCase
{
    public function testConstructor(): void
    {
        $media = $this->createStub(MediaInterface::class);

        $translation = new MediaTranslation($media, 'en', 'Sunset Photo', 'A beautiful sunset');

        $this->assertNotNull($translation->id());
        $this->assertSame($media, $translation->media());
        $this->assertSame('en', $translation->locale());
        $this->assertSame('Sunset Photo', $translation->name());
        $this->assertSame('A beautiful sunset', $translation->alt());
    }

    public function testChange(): void
    {
        $media = $this->createStub(MediaInterface::class);
        $translation = new MediaTranslation($media, 'en', 'Old Name', 'Old Alt');

        $translation->change('New Name', 'New Alt');

        $this->assertSame('New Name', $translation->name());
        $this->assertSame('New Alt', $translation->alt());
    }
}
