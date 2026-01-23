<?php

declare(strict_types=1);

namespace Xutim\EditorBundle\Tests\Unit\Domain\Template;

use PHPUnit\Framework\TestCase;
use Xutim\EditorBundle\Domain\Template\SlotDefinition;
use Xutim\EditorBundle\Entity\Block\ImageBlock;
use Xutim\EditorBundle\Entity\Block\ParagraphBlock;

final class SlotDefinitionTest extends TestCase
{
    public function testCanInstantiate(): void
    {
        $slot = new SlotDefinition(
            index: 0,
            label: 'Left Column',
            width: '50%'
        );

        $this->assertSame(0, $slot->index);
        $this->assertSame('Left Column', $slot->label);
        $this->assertSame('50%', $slot->width);
        $this->assertSame([], $slot->allowedBlockTypes);
        $this->assertSame(0, $slot->minBlocks);
        $this->assertNull($slot->maxBlocks);
    }

    public function testCanInstantiateWithAllowedBlockTypes(): void
    {
        $slot = new SlotDefinition(
            index: 1,
            label: 'Image Slot',
            width: '100%',
            allowedBlockTypes: [ImageBlock::class],
            minBlocks: 1,
            maxBlocks: 4
        );

        $this->assertSame([ImageBlock::class], $slot->allowedBlockTypes);
        $this->assertSame(1, $slot->minBlocks);
        $this->assertSame(4, $slot->maxBlocks);
    }

    public function testAllowsBlockTypeWithEmptyAllowedList(): void
    {
        $slot = new SlotDefinition(index: 0, label: 'Any');

        $this->assertTrue($slot->allowsBlockType(ParagraphBlock::class));
        $this->assertTrue($slot->allowsBlockType(ImageBlock::class));
    }

    public function testAllowsBlockTypeWithRestrictedList(): void
    {
        $slot = new SlotDefinition(
            index: 0,
            label: 'Images Only',
            allowedBlockTypes: [ImageBlock::class]
        );

        $this->assertTrue($slot->allowsBlockType(ImageBlock::class));
        $this->assertFalse($slot->allowsBlockType(ParagraphBlock::class));
    }

    public function testHasMaxLimit(): void
    {
        $slotWithLimit = new SlotDefinition(index: 0, label: 'Limited', maxBlocks: 5);
        $slotUnlimited = new SlotDefinition(index: 0, label: 'Unlimited');

        $this->assertTrue($slotWithLimit->hasMaxLimit());
        $this->assertFalse($slotUnlimited->hasMaxLimit());
    }

    public function testIsUnlimited(): void
    {
        $slotWithLimit = new SlotDefinition(index: 0, label: 'Limited', maxBlocks: 5);
        $slotUnlimited = new SlotDefinition(index: 0, label: 'Unlimited');

        $this->assertFalse($slotWithLimit->isUnlimited());
        $this->assertTrue($slotUnlimited->isUnlimited());
    }

    public function testNullWidth(): void
    {
        $slot = new SlotDefinition(index: 0, label: 'Auto Width');

        $this->assertNull($slot->width);
    }
}
