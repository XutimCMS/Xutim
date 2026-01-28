<?php

declare(strict_types=1);

namespace Xutim\EditorBundle\Domain\Factory;

use Xutim\CoreBundle\Domain\Model\FileInterface;
use Xutim\EditorBundle\Domain\Model\ContentBlockInterface;
use Xutim\EditorBundle\Domain\Model\ContentDraftInterface;
use Xutim\EditorBundle\Entity\Block\CodeBlock;
use Xutim\EditorBundle\Entity\Block\EmbedBlock;
use Xutim\EditorBundle\Entity\Block\HeadingBlock;
use Xutim\EditorBundle\Entity\Block\ImageBlock;
use Xutim\EditorBundle\Entity\Block\LayoutBlock;
use Xutim\EditorBundle\Entity\Block\ListItemBlock;
use Xutim\EditorBundle\Entity\Block\ParagraphBlock;
use Xutim\EditorBundle\Entity\Block\QuoteBlock;

class ContentBlockFactory
{
    /**
     * @param array<string, class-string<ContentBlockInterface>> $blockTypes
     */
    public function __construct(
        private readonly array $blockTypes = [],
    ) {
    }

    public function createParagraph(
        ContentDraftInterface $draft,
        string $html = '',
        ?ContentBlockInterface $parent = null,
        ?int $slot = null,
        int $position = 0,
    ): ParagraphBlock {
        return new ParagraphBlock($draft, $html, $parent, $slot, $position);
    }

    public function createHeading(
        ContentDraftInterface $draft,
        string $html = '',
        int $level = 2,
        ?ContentBlockInterface $parent = null,
        ?int $slot = null,
        int $position = 0,
    ): HeadingBlock {
        return new HeadingBlock($draft, $html, $level, $parent, $slot, $position);
    }

    public function createListItem(
        ContentDraftInterface $draft,
        string $html = '',
        string $listType = ListItemBlock::LIST_TYPE_UNORDERED,
        int $indent = 0,
        bool $checked = false,
        ?ContentBlockInterface $parent = null,
        ?int $slot = null,
        int $position = 0,
    ): ListItemBlock {
        return new ListItemBlock($draft, $html, $listType, $indent, $checked, $parent, $slot, $position);
    }

    public function createQuote(
        ContentDraftInterface $draft,
        string $html = '',
        ?string $attribution = null,
        ?ContentBlockInterface $parent = null,
        ?int $slot = null,
        int $position = 0,
    ): QuoteBlock {
        return new QuoteBlock($draft, $html, $attribution, $parent, $slot, $position);
    }

    public function createImage(
        ContentDraftInterface $draft,
        ?FileInterface $file = null,
        ?string $caption = null,
        ?ContentBlockInterface $parent = null,
        ?int $slot = null,
        int $position = 0,
    ): ImageBlock {
        return new ImageBlock($draft, $file, $caption, $parent, $slot, $position);
    }

    public function createEmbed(
        ContentDraftInterface $draft,
        string $service = EmbedBlock::SERVICE_OTHER,
        string $source = '',
        ?string $caption = null,
        ?ContentBlockInterface $parent = null,
        ?int $slot = null,
        int $position = 0,
    ): EmbedBlock {
        return new EmbedBlock($draft, $service, $source, $caption, $parent, $slot, $position);
    }

    public function createCode(
        ContentDraftInterface $draft,
        string $code = '',
        ?string $language = null,
        ?ContentBlockInterface $parent = null,
        ?int $slot = null,
        int $position = 0,
    ): CodeBlock {
        return new CodeBlock($draft, $code, $language, $parent, $slot, $position);
    }

    /**
     * @param array<string, mixed>|null $settings
     */
    public function createLayout(
        ContentDraftInterface $draft,
        string $template,
        ?array $settings = null,
        ?ContentBlockInterface $parent = null,
        ?int $slot = null,
        int $position = 0,
    ): LayoutBlock {
        return new LayoutBlock($draft, $template, $settings, $parent, $slot, $position);
    }

    /**
     * @return array<string, class-string<ContentBlockInterface>>
     */
    public function getBlockTypes(): array
    {
        return $this->blockTypes;
    }

    public function create(
        string $type,
        ContentDraftInterface $draft,
        ?ContentBlockInterface $parent = null,
        ?int $slot = null,
        int $position = 0,
    ): ContentBlockInterface {
        return match ($type) {
            ParagraphBlock::TYPE => $this->createParagraph($draft, '', $parent, $slot, $position),
            HeadingBlock::TYPE => $this->createHeading($draft, '', 2, $parent, $slot, $position),
            ListItemBlock::TYPE => $this->createListItem($draft, '', ListItemBlock::LIST_TYPE_UNORDERED, 0, false, $parent, $slot, $position),
            QuoteBlock::TYPE => $this->createQuote($draft, '', null, $parent, $slot, $position),
            ImageBlock::TYPE => $this->createImage($draft, null, null, $parent, $slot, $position),
            EmbedBlock::TYPE => $this->createEmbed($draft, EmbedBlock::SERVICE_OTHER, '', null, $parent, $slot, $position),
            CodeBlock::TYPE => $this->createCode($draft, '', null, $parent, $slot, $position),
            LayoutBlock::TYPE => $this->createLayout($draft, 'two_columns', null, $parent, $slot, $position),
            default => throw new \InvalidArgumentException(sprintf('Unknown block type: %s', $type)),
        };
    }
}
