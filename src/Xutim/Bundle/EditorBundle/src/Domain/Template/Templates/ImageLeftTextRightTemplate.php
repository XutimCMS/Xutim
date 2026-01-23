<?php

declare(strict_types=1);

namespace Xutim\EditorBundle\Domain\Template\Templates;

use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;
use Xutim\EditorBundle\Domain\Template\AbstractBlockTemplate;
use Xutim\EditorBundle\Domain\Template\SlotDefinition;
use Xutim\EditorBundle\Entity\Block\ImageBlock;
use Xutim\EditorBundle\Entity\Block\ParagraphBlock;

#[AutoconfigureTag('xutim.block_template')]
class ImageLeftTextRightTemplate extends AbstractBlockTemplate
{
    public function getName(): string
    {
        return 'image_left_text_right';
    }

    public function getLabel(): string
    {
        return 'Image Left, Text Right';
    }

    public function getDescription(): string
    {
        return 'Image (2/3) on the left, text content (1/3) on the right';
    }

    protected function defineSlots(): array
    {
        return [
            new SlotDefinition(
                index: 0,
                label: 'Image',
                width: '67%',
                allowedBlockTypes: [ImageBlock::class],
                maxBlocks: 1,
            ),
            new SlotDefinition(
                index: 1,
                label: 'Text',
                width: '33%',
                allowedBlockTypes: [ParagraphBlock::class],
            ),
        ];
    }
}
