<?php

declare(strict_types=1);

namespace Xutim\EditorBundle\Domain\Template\Templates;

use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;
use Xutim\EditorBundle\Domain\Template\AbstractBlockTemplate;
use Xutim\EditorBundle\Domain\Template\SlotDefinition;

#[AutoconfigureTag('xutim.block_template')]
class SidebarRightTemplate extends AbstractBlockTemplate
{
    public function getName(): string
    {
        return 'sidebar_right';
    }

    public function getLabel(): string
    {
        return 'Sidebar Right';
    }

    public function getDescription(): string
    {
        return 'Main content on the left, narrow sidebar on the right';
    }

    protected function defineSlots(): array
    {
        return [
            new SlotDefinition(index: 0, label: 'Main Content', width: '75%'),
            new SlotDefinition(index: 1, label: 'Sidebar', width: '25%'),
        ];
    }
}
