<?php

declare(strict_types=1);

namespace Xutim\EditorBundle\Tests\Unit\Entity\Block;

use PHPUnit\Framework\TestCase;
use Xutim\CoreBundle\Domain\Model\ContentTranslationInterface;
use Xutim\EditorBundle\Entity\Block\ListItemBlock;
use Xutim\EditorBundle\Entity\ContentDraft;

final class ListItemBlockTest extends TestCase
{
    private ContentDraft $draft;

    protected function setUp(): void
    {
        $translation = $this->createMock(ContentTranslationInterface::class);
        $this->draft = new ContentDraft($translation);
    }

    public function testCanInstantiate(): void
    {
        $block = new ListItemBlock($this->draft, 'Item text');

        $this->assertSame('list_item', $block->getType());
        $this->assertSame('Item text', $block->getHtml());
        $this->assertSame(ListItemBlock::LIST_TYPE_UNORDERED, $block->getListType());
        $this->assertSame(0, $block->getIndent());
        $this->assertFalse($block->isChecked());
    }

    public function testOrderedList(): void
    {
        $block = new ListItemBlock(
            $this->draft,
            'Item',
            ListItemBlock::LIST_TYPE_ORDERED
        );

        $this->assertSame(ListItemBlock::LIST_TYPE_ORDERED, $block->getListType());
        $this->assertTrue($block->isOrdered());
        $this->assertFalse($block->isUnordered());
        $this->assertFalse($block->isChecklist());
    }

    public function testUnorderedList(): void
    {
        $block = new ListItemBlock(
            $this->draft,
            'Item',
            ListItemBlock::LIST_TYPE_UNORDERED
        );

        $this->assertTrue($block->isUnordered());
        $this->assertFalse($block->isOrdered());
        $this->assertFalse($block->isChecklist());
    }

    public function testChecklist(): void
    {
        $block = new ListItemBlock(
            $this->draft,
            'Task',
            ListItemBlock::LIST_TYPE_CHECKLIST,
            0,
            true
        );

        $this->assertTrue($block->isChecklist());
        $this->assertTrue($block->isChecked());
        $this->assertFalse($block->isOrdered());
        $this->assertFalse($block->isUnordered());
    }

    public function testNestedIndent(): void
    {
        $block = new ListItemBlock(
            $this->draft,
            'Nested item',
            ListItemBlock::LIST_TYPE_UNORDERED,
            2
        );

        $this->assertSame(2, $block->getIndent());
    }

    public function testSetHtml(): void
    {
        $block = new ListItemBlock($this->draft, 'Initial');

        $block->setHtml('Updated');

        $this->assertSame('Updated', $block->getHtml());
    }

    public function testSetListType(): void
    {
        $block = new ListItemBlock($this->draft, 'Item');

        $block->setListType(ListItemBlock::LIST_TYPE_ORDERED);

        $this->assertSame(ListItemBlock::LIST_TYPE_ORDERED, $block->getListType());
    }

    public function testSetIndent(): void
    {
        $block = new ListItemBlock($this->draft, 'Item');

        $block->setIndent(3);

        $this->assertSame(3, $block->getIndent());
    }

    public function testSetChecked(): void
    {
        $block = new ListItemBlock(
            $this->draft,
            'Task',
            ListItemBlock::LIST_TYPE_CHECKLIST
        );

        $this->assertFalse($block->isChecked());

        $block->setChecked(true);

        $this->assertTrue($block->isChecked());
    }
}
