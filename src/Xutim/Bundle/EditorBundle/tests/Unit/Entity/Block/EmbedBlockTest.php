<?php

declare(strict_types=1);

namespace Xutim\EditorBundle\Tests\Unit\Entity\Block;

use PHPUnit\Framework\TestCase;
use Xutim\CoreBundle\Domain\Model\ContentTranslationInterface;
use Xutim\EditorBundle\Entity\Block\EmbedBlock;
use Xutim\EditorBundle\Entity\ContentDraft;

final class EmbedBlockTest extends TestCase
{
    private ContentDraft $draft;

    protected function setUp(): void
    {
        $translation = $this->createMock(ContentTranslationInterface::class);
        $this->draft = new ContentDraft($translation);
    }

    public function testCanInstantiate(): void
    {
        $block = new EmbedBlock(
            $this->draft,
            EmbedBlock::SERVICE_YOUTUBE,
            'dQw4w9WgXcQ',
            'Video caption'
        );

        $this->assertSame('embed', $block->getType());
        $this->assertSame(EmbedBlock::SERVICE_YOUTUBE, $block->getService());
        $this->assertSame('dQw4w9WgXcQ', $block->getSource());
        $this->assertSame('Video caption', $block->getCaption());
    }

    public function testDefaultService(): void
    {
        $block = new EmbedBlock($this->draft);

        $this->assertSame(EmbedBlock::SERVICE_OTHER, $block->getService());
        $this->assertSame('', $block->getSource());
    }

    public function testIsYoutube(): void
    {
        $block = new EmbedBlock($this->draft, EmbedBlock::SERVICE_YOUTUBE, 'abc123');

        $this->assertTrue($block->isYoutube());
        $this->assertFalse($block->isVimeo());
    }

    public function testIsVimeo(): void
    {
        $block = new EmbedBlock($this->draft, EmbedBlock::SERVICE_VIMEO, '123456');

        $this->assertTrue($block->isVimeo());
        $this->assertFalse($block->isYoutube());
    }

    public function testSetService(): void
    {
        $block = new EmbedBlock($this->draft);

        $block->setService(EmbedBlock::SERVICE_TWITTER);

        $this->assertSame(EmbedBlock::SERVICE_TWITTER, $block->getService());
    }

    public function testSetSource(): void
    {
        $block = new EmbedBlock($this->draft);

        $block->setSource('https://example.com/embed');

        $this->assertSame('https://example.com/embed', $block->getSource());
    }

    public function testSetCaption(): void
    {
        $block = new EmbedBlock($this->draft);

        $block->setCaption('Embed caption');

        $this->assertSame('Embed caption', $block->getCaption());
    }
}
