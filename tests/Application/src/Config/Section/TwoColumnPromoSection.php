<?php

declare(strict_types=1);

namespace App\Config\Section;

use Xutim\CoreBundle\Config\Layout\Block\Option\ImageBlockItemOption;
use Xutim\CoreBundle\Config\Layout\Block\Option\LinkBlockItemOption;
use Xutim\CoreBundle\Config\Layout\Block\Option\PageBlockItemOption;
use Xutim\CoreBundle\Config\Layout\Block\Option\TextBlockItemOption;
use Xutim\CoreBundle\Config\Section\SectionDefinition;

final class TwoColumnPromoSection implements SectionDefinition
{
    public function getCode(): string
    {
        return 'two-column-promo';
    }

    public function getName(): string
    {
        return 'Two column promo';
    }

    public function getFields(): array
    {
        return [
            'title' => new TextBlockItemOption(),
            'page1' => new PageBlockItemOption(),
            'image' => new ImageBlockItemOption(),
            'link' => new LinkBlockItemOption(),
        ];
    }

    public function getFieldDescriptions(): array
    {
        return [];
    }

    public function getTemplate(): string
    {
        return 'section/two_column_promo.html.twig';
    }

    public function getFormBodyTemplate(): ?string
    {
        return null;
    }

    public function getDescription(): string
    {
        return '';
    }

    public function getCategory(): string
    {
        return 'Test';
    }

    public function getPreviewImage(): string
    {
        return '';
    }
}
