<?php

declare(strict_types=1);

namespace Xutim\EditorBundle\Domain\Template\Templates;

use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;
use Xutim\EditorBundle\Domain\Template\AbstractBlockTemplate;
use Xutim\EditorBundle\Domain\Template\SlotDefinition;
use Xutim\EditorBundle\Entity\Block\ImageBlock;
use Xutim\EditorBundle\Entity\Block\ParagraphBlock;

#[AutoconfigureTag('xutim.block_template')]
class TextLeftImageRightTemplate extends AbstractBlockTemplate
{
    public function getName(): string
    {
        return 'text_left_image_right';
    }

    public function getLabel(): string
    {
        return 'Text Left, Image Right';
    }

    public function getDescription(): string
    {
        return 'Text content (1/3) on the left, image (2/3) on the right';
    }

    protected function defineSlots(): array
    {
        return [
            new SlotDefinition(
                index: 0,
                label: 'Text',
                width: '33%',
                allowedBlockTypes: [ParagraphBlock::class],
            ),
            new SlotDefinition(
                index: 1,
                label: 'Image',
                width: '67%',
                allowedBlockTypes: [ImageBlock::class],
                maxBlocks: 1,
            ),
        ];
    }
}
