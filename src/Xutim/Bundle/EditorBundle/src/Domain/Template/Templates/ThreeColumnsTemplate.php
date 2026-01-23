<?php

declare(strict_types=1);

namespace Xutim\EditorBundle\Domain\Template\Templates;

use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;
use Xutim\EditorBundle\Domain\Template\AbstractBlockTemplate;
use Xutim\EditorBundle\Domain\Template\SlotDefinition;

#[AutoconfigureTag('xutim.block_template')]
class ThreeColumnsTemplate extends AbstractBlockTemplate
{
    public function getName(): string
    {
        return 'three_columns';
    }

    public function getLabel(): string
    {
        return 'Three Columns';
    }

    public function getDescription(): string
    {
        return 'Three equal-width columns side by side';
    }

    protected function defineSlots(): array
    {
        return [
            new SlotDefinition(index: 0, label: 'Left Column', width: '33%'),
            new SlotDefinition(index: 1, label: 'Center Column', width: '34%'),
            new SlotDefinition(index: 2, label: 'Right Column', width: '33%'),
        ];
    }
}
