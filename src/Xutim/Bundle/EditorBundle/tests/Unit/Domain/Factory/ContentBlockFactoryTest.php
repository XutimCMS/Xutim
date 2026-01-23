<?php

declare(strict_types=1);

namespace Xutim\EditorBundle\Tests\Unit\Domain\Factory;

use PHPUnit\Framework\TestCase;
use Xutim\CoreBundle\Domain\Model\ContentTranslationInterface;
use Xutim\CoreBundle\Domain\Model\FileInterface;
use Xutim\EditorBundle\Domain\Factory\ContentBlockFactory;
use Xutim\EditorBundle\Entity\Block\CodeBlock;
use Xutim\EditorBundle\Entity\Block\EmbedBlock;
use Xutim\EditorBundle\Entity\Block\HeadingBlock;
use Xutim\EditorBundle\Entity\Block\ImageBlock;
use Xutim\EditorBundle\Entity\Block\LayoutBlock;
use Xutim\EditorBundle\Entity\Block\ListItemBlock;
use Xutim\EditorBundle\Entity\Block\ParagraphBlock;
use Xutim\EditorBundle\Entity\Block\QuoteBlock;
use Xutim\EditorBundle\Entity\ContentDraft;

final class ContentBlockFactoryTest extends TestCase
{
    private ContentBlockFactory $factory;
    private ContentDraft $draft;

    protected function setUp(): void
    {
        $this->factory = new ContentBlockFactory();
        $translation = $this->createMock(ContentTranslationInterface::class);
        $this->draft = new ContentDraft($translation);
    }

    public function testCreateParagraph(): void
    {
        $block = $this->factory->createParagraph($this->draft, '<p>Test</p>');

        $this->assertInstanceOf(ParagraphBlock::class, $block);
        $this->assertSame('<p>Test</p>', $block->getHtml());
        $this->assertSame($this->draft, $block->getDraft());
    }

    public function testCreateParagraphWithSlotAndPosition(): void
    {
        $parent = $this->factory->createLayout($this->draft, 'two_columns');

        $block = $this->factory->createParagraph(
            $this->draft,
            'Text',
            $parent,
            0,
            5
        );

        $this->assertSame($parent, $block->getParent());
        $this->assertSame(0, $block->getSlot());
        $this->assertSame(5, $block->getPosition());
    }

    public function testCreateHeading(): void
    {
        $block = $this->factory->createHeading($this->draft, 'My Title', 2);

        $this->assertInstanceOf(HeadingBlock::class, $block);
        $this->assertSame('My Title', $block->getHtml());
        $this->assertSame(2, $block->getLevel());
    }

    public function testCreateHeadingWithDifferentLevels(): void
    {
        $h1 = $this->factory->createHeading($this->draft, 'H1', 1);
        $h3 = $this->factory->createHeading($this->draft, 'H3', 3);
        $h4 = $this->factory->createHeading($this->draft, 'H4', 4);

        $this->assertSame(1, $h1->getLevel());
        $this->assertSame(3, $h3->getLevel());
        $this->assertSame(4, $h4->getLevel());
    }

    public function testCreateListItem(): void
    {
        $block = $this->factory->createListItem(
            $this->draft,
            'Item text',
            ListItemBlock::LIST_TYPE_ORDERED,
            1,
            false
        );

        $this->assertInstanceOf(ListItemBlock::class, $block);
        $this->assertSame('Item text', $block->getHtml());
        $this->assertSame(ListItemBlock::LIST_TYPE_ORDERED, $block->getListType());
        $this->assertSame(1, $block->getIndent());
        $this->assertFalse($block->isChecked());
    }

    public function testCreateChecklistItem(): void
    {
        $block = $this->factory->createListItem(
            $this->draft,
            'Task',
            ListItemBlock::LIST_TYPE_CHECKLIST,
            0,
            true
        );

        $this->assertTrue($block->isChecklist());
        $this->assertTrue($block->isChecked());
    }

    public function testCreateQuote(): void
    {
        $block = $this->factory->createQuote($this->draft, 'Quote text', 'Author');

        $this->assertInstanceOf(QuoteBlock::class, $block);
        $this->assertSame('Quote text', $block->getHtml());
        $this->assertSame('Author', $block->getAttribution());
    }

    public function testCreateImage(): void
    {
        $file = $this->createMock(FileInterface::class);

        $block = $this->factory->createImage($this->draft, $file, 'Caption');

        $this->assertInstanceOf(ImageBlock::class, $block);
        $this->assertSame($file, $block->getFile());
        $this->assertSame('Caption', $block->getCaption());
    }

    public function testCreateImageWithoutFile(): void
    {
        $block = $this->factory->createImage($this->draft);

        $this->assertNull($block->getFile());
        $this->assertFalse($block->hasFile());
    }

    public function testCreateEmbed(): void
    {
        $block = $this->factory->createEmbed(
            $this->draft,
            EmbedBlock::SERVICE_YOUTUBE,
            'dQw4w9WgXcQ',
            'Video'
        );

        $this->assertInstanceOf(EmbedBlock::class, $block);
        $this->assertSame(EmbedBlock::SERVICE_YOUTUBE, $block->getService());
        $this->assertSame('dQw4w9WgXcQ', $block->getSource());
        $this->assertSame('Video', $block->getCaption());
    }

    public function testCreateCode(): void
    {
        $block = $this->factory->createCode($this->draft, 'echo "Hello";', 'php');

        $this->assertInstanceOf(CodeBlock::class, $block);
        $this->assertSame('echo "Hello";', $block->getCode());
        $this->assertSame('php', $block->getLanguage());
    }

    public function testCreateLayout(): void
    {
        $settings = ['gap' => '10px'];

        $block = $this->factory->createLayout($this->draft, 'two_columns', $settings);

        $this->assertInstanceOf(LayoutBlock::class, $block);
        $this->assertSame('two_columns', $block->getTemplate());
        $this->assertSame($settings, $block->getSettings());
    }

    public function testGetBlockTypes(): void
    {
        $blockTypes = [
            'paragraph' => ParagraphBlock::class,
            'heading' => HeadingBlock::class,
        ];
        $factory = new ContentBlockFactory($blockTypes);

        $this->assertSame($blockTypes, $factory->getBlockTypes());
    }
}
