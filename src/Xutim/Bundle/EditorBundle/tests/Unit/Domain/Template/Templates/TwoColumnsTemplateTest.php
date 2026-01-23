<?php

declare(strict_types=1);

namespace Xutim\EditorBundle\Tests\Unit\Domain\Template\Templates;

use PHPUnit\Framework\TestCase;
use Xutim\EditorBundle\Domain\Template\BlockTemplateInterface;
use Xutim\EditorBundle\Domain\Template\Templates\TwoColumnsTemplate;

final class TwoColumnsTemplateTest extends TestCase
{
    private TwoColumnsTemplate $template;

    protected function setUp(): void
    {
        $this->template = new TwoColumnsTemplate();
    }

    public function testImplementsInterface(): void
    {
        $this->assertInstanceOf(BlockTemplateInterface::class, $this->template);
    }

    public function testGetName(): void
    {
        $this->assertSame('two_columns', $this->template->getName());
    }

    public function testGetLabel(): void
    {
        $this->assertSame('Two Columns', $this->template->getLabel());
    }

    public function testGetDescription(): void
    {
        $this->assertNotEmpty($this->template->getDescription());
    }

    public function testGetSlotCount(): void
    {
        $this->assertSame(2, $this->template->getSlotCount());
    }

    public function testGetSlots(): void
    {
        $slots = $this->template->getSlots();

        $this->assertCount(2, $slots);

        $this->assertSame(0, $slots[0]->index);
        $this->assertSame('Left Column', $slots[0]->label);
        $this->assertSame('50%', $slots[0]->width);

        $this->assertSame(1, $slots[1]->index);
        $this->assertSame('Right Column', $slots[1]->label);
        $this->assertSame('50%', $slots[1]->width);
    }

    public function testGetSlot(): void
    {
        $slot0 = $this->template->getSlot(0);
        $slot1 = $this->template->getSlot(1);
        $slotInvalid = $this->template->getSlot(99);

        $this->assertNotNull($slot0);
        $this->assertSame(0, $slot0->index);

        $this->assertNotNull($slot1);
        $this->assertSame(1, $slot1->index);

        $this->assertNull($slotInvalid);
    }
}
