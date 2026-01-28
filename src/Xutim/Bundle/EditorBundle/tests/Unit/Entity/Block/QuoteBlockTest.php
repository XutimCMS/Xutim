<?php

declare(strict_types=1);

namespace Xutim\EditorBundle\Tests\Unit\Entity\Block;

use PHPUnit\Framework\TestCase;
use Xutim\CoreBundle\Domain\Model\ContentTranslationInterface;
use Xutim\EditorBundle\Entity\Block\QuoteBlock;
use Xutim\EditorBundle\Entity\ContentDraft;

final class QuoteBlockTest extends TestCase
{
    private ContentDraft $draft;

    protected function setUp(): void
    {
        $translation = $this->createStub(ContentTranslationInterface::class);
        $this->draft = new ContentDraft($translation);
    }

    public function testCanInstantiate(): void
    {
        $block = new QuoteBlock($this->draft, 'Quote text', 'Author Name');

        $this->assertSame('quote', $block->getType());
        $this->assertSame('Quote text', $block->getHtml());
        $this->assertSame('Author Name', $block->getAttribution());
    }

    public function testCanInstantiateWithoutAttribution(): void
    {
        $block = new QuoteBlock($this->draft, 'Quote text');

        $this->assertSame('Quote text', $block->getHtml());
        $this->assertNull($block->getAttribution());
    }

    public function testSetHtml(): void
    {
        $block = new QuoteBlock($this->draft, 'Initial');

        $block->setHtml('Updated quote');

        $this->assertSame('Updated quote', $block->getHtml());
    }

    public function testSetAttribution(): void
    {
        $block = new QuoteBlock($this->draft, 'Quote');

        $block->setAttribution('New Author');

        $this->assertSame('New Author', $block->getAttribution());
    }

    public function testClearAttribution(): void
    {
        $block = new QuoteBlock($this->draft, 'Quote', 'Author');

        $block->setAttribution(null);

        $this->assertNull($block->getAttribution());
    }
}
