<?php

declare(strict_types=1);

namespace Xutim\EditorBundle\Tests\Unit\Domain\Template\Templates;

use PHPUnit\Framework\TestCase;
use Xutim\EditorBundle\Domain\Template\Templates\TextLeftImageRightTemplate;
use Xutim\EditorBundle\Entity\Block\ImageBlock;
use Xutim\EditorBundle\Entity\Block\ParagraphBlock;

final class TextLeftImageRightTemplateTest extends TestCase
{
    private TextLeftImageRightTemplate $template;

    protected function setUp(): void
    {
        $this->template = new TextLeftImageRightTemplate();
    }

    public function testGetName(): void
    {
        $this->assertSame('text_left_image_right', $this->template->getName());
    }

    public function testGetSlotCount(): void
    {
        $this->assertSame(2, $this->template->getSlotCount());
    }

    public function testSlotWidths(): void
    {
        $slots = $this->template->getSlots();

        $this->assertSame('33%', $slots[0]->width);
        $this->assertSame('67%', $slots[1]->width);
    }

    public function testTextSlotAllowedTypes(): void
    {
        $textSlot = $this->template->getSlot(0);

        $this->assertNotNull($textSlot);
        $this->assertTrue($textSlot->allowsBlockType(ParagraphBlock::class));
        $this->assertFalse($textSlot->allowsBlockType(ImageBlock::class));
    }

    public function testImageSlotAllowedTypes(): void
    {
        $imageSlot = $this->template->getSlot(1);

        $this->assertNotNull($imageSlot);
        $this->assertTrue($imageSlot->allowsBlockType(ImageBlock::class));
        $this->assertFalse($imageSlot->allowsBlockType(ParagraphBlock::class));
    }

    public function testImageSlotMaxBlocks(): void
    {
        $imageSlot = $this->template->getSlot(1);

        $this->assertNotNull($imageSlot);
        $this->assertSame(1, $imageSlot->maxBlocks);
        $this->assertTrue($imageSlot->hasMaxLimit());
    }
}
