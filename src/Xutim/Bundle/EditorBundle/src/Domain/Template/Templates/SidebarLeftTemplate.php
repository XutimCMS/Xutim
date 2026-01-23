<?php

declare(strict_types=1);

namespace Xutim\EditorBundle\Domain\Template\Templates;

use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;
use Xutim\EditorBundle\Domain\Template\AbstractBlockTemplate;
use Xutim\EditorBundle\Domain\Template\SlotDefinition;

#[AutoconfigureTag('xutim.block_template')]
class SidebarLeftTemplate extends AbstractBlockTemplate
{
    public function getName(): string
    {
        return 'sidebar_left';
    }

    public function getLabel(): string
    {
        return 'Sidebar Left';
    }

    public function getDescription(): string
    {
        return 'Narrow sidebar on the left, main content on the right';
    }

    protected function defineSlots(): array
    {
        return [
            new SlotDefinition(index: 0, label: 'Sidebar', width: '25%'),
            new SlotDefinition(index: 1, label: 'Main Content', width: '75%'),
        ];
    }
}
