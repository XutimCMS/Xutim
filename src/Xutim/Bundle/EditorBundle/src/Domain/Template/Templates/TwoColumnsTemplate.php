<?php

declare(strict_types=1);

namespace Xutim\EditorBundle\Domain\Template\Templates;

use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;
use Xutim\EditorBundle\Domain\Template\AbstractBlockTemplate;
use Xutim\EditorBundle\Domain\Template\SlotDefinition;

#[AutoconfigureTag('xutim.block_template')]
class TwoColumnsTemplate extends AbstractBlockTemplate
{
    public function getName(): string
    {
        return 'two_columns';
    }

    public function getLabel(): string
    {
        return 'Two Columns';
    }

    public function getDescription(): string
    {
        return 'Two equal-width columns side by side';
    }

    protected function defineSlots(): array
    {
        return [
            new SlotDefinition(index: 0, label: 'Left Column', width: '50%'),
            new SlotDefinition(index: 1, label: 'Right Column', width: '50%'),
        ];
    }
}
