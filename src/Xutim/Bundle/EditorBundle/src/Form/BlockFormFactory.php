<?php

declare(strict_types=1);

namespace Xutim\EditorBundle\Form;

use Xutim\EditorBundle\Domain\Model\ContentBlockInterface;
use Xutim\EditorBundle\Entity\Block\CodeBlock;
use Xutim\EditorBundle\Entity\Block\EmbedBlock;
use Xutim\EditorBundle\Entity\Block\HeadingBlock;
use Xutim\EditorBundle\Entity\Block\ImageBlock;
use Xutim\EditorBundle\Entity\Block\LayoutBlock;
use Xutim\EditorBundle\Entity\Block\ListItemBlock;
use Xutim\EditorBundle\Entity\Block\ParagraphBlock;
use Xutim\EditorBundle\Entity\Block\QuoteBlock;

final class BlockFormFactory
{
    /**
     * @var array<string, class-string>
     */
    private const array FORM_TYPE_MAP = [
        ParagraphBlock::TYPE => ParagraphBlockType::class,
        HeadingBlock::TYPE => HeadingBlockType::class,
        ListItemBlock::TYPE => ListItemBlockType::class,
        QuoteBlock::TYPE => QuoteBlockType::class,
        ImageBlock::TYPE => ImageBlockType::class,
        EmbedBlock::TYPE => EmbedBlockType::class,
        CodeBlock::TYPE => CodeBlockType::class,
        LayoutBlock::TYPE => LayoutBlockType::class,
    ];

    /**
     * @return class-string
     */
    public function getFormTypeClass(ContentBlockInterface $block): string
    {
        $type = $block->getType();

        if (!isset(self::FORM_TYPE_MAP[$type])) {
            throw new \InvalidArgumentException(sprintf('Unknown block type: %s', $type));
        }

        return self::FORM_TYPE_MAP[$type];
    }

    /**
     * @return class-string
     */
    public function getFormTypeClassByType(string $type): string
    {
        if (!isset(self::FORM_TYPE_MAP[$type])) {
            throw new \InvalidArgumentException(sprintf('Unknown block type: %s', $type));
        }

        return self::FORM_TYPE_MAP[$type];
    }

    /**
     * @return array<string, class-string>
     */
    public function getAvailableBlockTypes(): array
    {
        return self::FORM_TYPE_MAP;
    }
}
