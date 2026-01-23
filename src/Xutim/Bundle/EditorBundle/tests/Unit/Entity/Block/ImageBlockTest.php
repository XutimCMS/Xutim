<?php

declare(strict_types=1);

namespace Xutim\EditorBundle\Tests\Unit\Entity\Block;

use PHPUnit\Framework\TestCase;
use Xutim\CoreBundle\Domain\Model\ContentTranslationInterface;
use Xutim\CoreBundle\Domain\Model\FileInterface;
use Xutim\EditorBundle\Entity\Block\ImageBlock;
use Xutim\EditorBundle\Entity\ContentDraft;

final class ImageBlockTest extends TestCase
{
    private ContentDraft $draft;

    protected function setUp(): void
    {
        $translation = $this->createMock(ContentTranslationInterface::class);
        $this->draft = new ContentDraft($translation);
    }

    public function testCanInstantiate(): void
    {
        $file = $this->createMock(FileInterface::class);
        $block = new ImageBlock($this->draft, $file, 'Image caption');

        $this->assertSame('image', $block->getType());
        $this->assertSame($file, $block->getFile());
        $this->assertSame('Image caption', $block->getCaption());
        $this->assertTrue($block->hasFile());
    }

    public function testCanInstantiateWithoutFile(): void
    {
        $block = new ImageBlock($this->draft);

        $this->assertNull($block->getFile());
        $this->assertNull($block->getCaption());
        $this->assertFalse($block->hasFile());
    }

    public function testSetFile(): void
    {
        $block = new ImageBlock($this->draft);
        $file = $this->createMock(FileInterface::class);

        $block->setFile($file);

        $this->assertSame($file, $block->getFile());
        $this->assertTrue($block->hasFile());
    }

    public function testClearFile(): void
    {
        $file = $this->createMock(FileInterface::class);
        $block = new ImageBlock($this->draft, $file);

        $block->setFile(null);

        $this->assertNull($block->getFile());
        $this->assertFalse($block->hasFile());
    }

    public function testSetCaption(): void
    {
        $block = new ImageBlock($this->draft);

        $block->setCaption('New caption');

        $this->assertSame('New caption', $block->getCaption());
    }
}
