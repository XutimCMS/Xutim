<?php

declare(strict_types=1);

namespace Xutim\EditorBundle\Tests\Unit\Entity\Block;

use PHPUnit\Framework\TestCase;
use Xutim\CoreBundle\Domain\Model\ContentTranslationInterface;
use Xutim\EditorBundle\Domain\Model\ContentBlockInterface;
use Xutim\EditorBundle\Entity\Block\ParagraphBlock;
use Xutim\EditorBundle\Entity\ContentDraft;

final class ParagraphBlockTest extends TestCase
{
    private ContentDraft $draft;

    protected function setUp(): void
    {
        $translation = $this->createStub(ContentTranslationInterface::class);
        $this->draft = new ContentDraft($translation);
    }

    public function testCanInstantiate(): void
    {
        $block = new ParagraphBlock($this->draft, '<p>Hello world</p>');

        $this->assertInstanceOf(ContentBlockInterface::class, $block);
        $this->assertSame('paragraph', $block->getType());
        $this->assertSame('<p>Hello world</p>', $block->getHtml());
        $this->assertSame($this->draft, $block->getDraft());
        $this->assertNull($block->getParent());
        $this->assertNull($block->getSlot());
        $this->assertSame(0, $block->getPosition());
    }

    public function testCanInstantiateWithSlotAndPosition(): void
    {
        $parentBlock = new ParagraphBlock($this->draft);
        $block = new ParagraphBlock(
            $this->draft,
            'Text',
            $parentBlock,
            1,
            5
        );

        $this->assertSame($parentBlock, $block->getParent());
        $this->assertSame(1, $block->getSlot());
        $this->assertSame(5, $block->getPosition());
    }

    public function testSetHtml(): void
    {
        $block = new ParagraphBlock($this->draft, 'Initial');
        $originalUpdatedAt = $block->getUpdatedAt();

        usleep(1000);
        $block->setHtml('Updated content');

        $this->assertSame('Updated content', $block->getHtml());
        $this->assertGreaterThan($originalUpdatedAt, $block->getUpdatedAt());
    }

    public function testSetParent(): void
    {
        $block = new ParagraphBlock($this->draft);
        $parentBlock = new ParagraphBlock($this->draft);

        $block->setParent($parentBlock);

        $this->assertSame($parentBlock, $block->getParent());
    }

    public function testSetSlot(): void
    {
        $block = new ParagraphBlock($this->draft);

        $block->setSlot(2);

        $this->assertSame(2, $block->getSlot());
    }

    public function testSetPosition(): void
    {
        $block = new ParagraphBlock($this->draft);

        $block->setPosition(10);

        $this->assertSame(10, $block->getPosition());
    }

    public function testBlockIsAddedToDraft(): void
    {
        $this->assertCount(0, $this->draft->getBlocks());

        new ParagraphBlock($this->draft, 'Test');

        $this->assertCount(1, $this->draft->getBlocks());
    }
}
