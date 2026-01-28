<?php

declare(strict_types=1);

namespace Xutim\EditorBundle\Tests\Unit\Entity\Block;

use PHPUnit\Framework\TestCase;
use Xutim\CoreBundle\Domain\Model\ContentTranslationInterface;
use Xutim\EditorBundle\Entity\Block\LayoutBlock;
use Xutim\EditorBundle\Entity\ContentDraft;

final class LayoutBlockTest extends TestCase
{
    private ContentDraft $draft;

    protected function setUp(): void
    {
        $translation = $this->createStub(ContentTranslationInterface::class);
        $this->draft = new ContentDraft($translation);
    }

    public function testCanInstantiate(): void
    {
        $block = new LayoutBlock($this->draft, 'two_columns');

        $this->assertSame('layout', $block->getType());
        $this->assertSame('two_columns', $block->getTemplate());
        $this->assertNull($block->getSettings());
    }

    public function testCanInstantiateWithSettings(): void
    {
        $settings = ['gap' => '20px', 'align' => 'center'];
        $block = new LayoutBlock($this->draft, 'three_columns', $settings);

        $this->assertSame($settings, $block->getSettings());
    }

    public function testSetTemplate(): void
    {
        $block = new LayoutBlock($this->draft, 'two_columns');

        $block->setTemplate('sidebar_left');

        $this->assertSame('sidebar_left', $block->getTemplate());
    }

    public function testSetSettings(): void
    {
        $block = new LayoutBlock($this->draft, 'two_columns');
        $settings = ['background' => '#fff'];

        $block->setSettings($settings);

        $this->assertSame($settings, $block->getSettings());
    }

    public function testGetSetting(): void
    {
        $settings = ['gap' => '20px', 'align' => 'center'];
        $block = new LayoutBlock($this->draft, 'two_columns', $settings);

        $this->assertSame('20px', $block->getSetting('gap'));
        $this->assertSame('center', $block->getSetting('align'));
    }

    public function testGetSettingWithDefault(): void
    {
        $block = new LayoutBlock($this->draft, 'two_columns');

        $this->assertNull($block->getSetting('nonexistent'));
        $this->assertSame('default', $block->getSetting('nonexistent', 'default'));
    }

    public function testGetSettingFromNullSettings(): void
    {
        $block = new LayoutBlock($this->draft, 'two_columns');

        $this->assertSame('fallback', $block->getSetting('key', 'fallback'));
    }
}
