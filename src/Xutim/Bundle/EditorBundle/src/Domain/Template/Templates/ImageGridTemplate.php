<?php

declare(strict_types=1);

namespace Xutim\EditorBundle\Domain\Template\Templates;

use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;
use Xutim\EditorBundle\Domain\Template\AbstractBlockTemplate;
use Xutim\EditorBundle\Domain\Template\SlotDefinition;
use Xutim\EditorBundle\Entity\Block\ImageBlock;

#[AutoconfigureTag('xutim.block_template')]
class ImageGridTemplate extends AbstractBlockTemplate
{
    public function getName(): string
    {
        return 'image_grid';
    }

    public function getLabel(): string
    {
        return 'Image Grid';
    }

    public function getDescription(): string
    {
        return 'Grid of images (2-4 images per row)';
    }

    protected function defineSlots(): array
    {
        return [
            new SlotDefinition(
                index: 0,
                label: 'Images',
                width: '100%',
                allowedBlockTypes: [ImageBlock::class],
                minBlocks: 2,
                maxBlocks: 4,
            ),
        ];
    }
}
