<?php

declare(strict_types=1);

namespace Xutim\EditorBundle\Tests\Unit\Domain\Template\Templates;

use PHPUnit\Framework\TestCase;
use Xutim\EditorBundle\Domain\Template\Templates\ImageGridTemplate;
use Xutim\EditorBundle\Entity\Block\ImageBlock;
use Xutim\EditorBundle\Entity\Block\ParagraphBlock;

final class ImageGridTemplateTest extends TestCase
{
    private ImageGridTemplate $template;

    protected function setUp(): void
    {
        $this->template = new ImageGridTemplate();
    }

    public function testGetName(): void
    {
        $this->assertSame('image_grid', $this->template->getName());
    }

    public function testGetSlotCount(): void
    {
        $this->assertSame(1, $this->template->getSlotCount());
    }

    public function testSlotOnlyAllowsImages(): void
    {
        $slot = $this->template->getSlot(0);

        $this->assertNotNull($slot);
        $this->assertTrue($slot->allowsBlockType(ImageBlock::class));
        $this->assertFalse($slot->allowsBlockType(ParagraphBlock::class));
    }

    public function testSlotMinMaxBlocks(): void
    {
        $slot = $this->template->getSlot(0);

        $this->assertNotNull($slot);
        $this->assertSame(2, $slot->minBlocks);
        $this->assertSame(4, $slot->maxBlocks);
    }
}
