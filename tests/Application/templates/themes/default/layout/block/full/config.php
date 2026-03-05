<?php

declare(strict_types=1);

use Xutim\CoreBundle\Config\Layout\Block\Option\ArticleBlockItemOption;
use Xutim\CoreBundle\Config\Layout\Block\Option\BlockItemOptionCollection;
use Xutim\CoreBundle\Config\Layout\Block\Option\FileBlockItemOption;
use Xutim\CoreBundle\Config\Layout\Block\Option\LinkBlockItemOption;
use Xutim\CoreBundle\Config\Layout\Block\Option\MediaFolderBlockItemOption;
use Xutim\CoreBundle\Config\Layout\Block\Option\PageBlockItemOption;
use Xutim\CoreBundle\Config\Layout\Block\Option\SnippetBlockItemOption;
use Xutim\CoreBundle\Config\Layout\Block\Option\TagBlockItemOption;
use Xutim\CoreBundle\Config\Layout\Block\Option\TextBlockItemOption;
use Xutim\CoreBundle\Config\Layout\LayoutConfig;

return new LayoutConfig(
    code: 'full',
    name: 'Full Block',
    config: [
        new BlockItemOptionCollection(new PageBlockItemOption()),
        new BlockItemOptionCollection(new ArticleBlockItemOption()),
        new BlockItemOptionCollection(new FileBlockItemOption()),
        new BlockItemOptionCollection(new SnippetBlockItemOption()),
        new BlockItemOptionCollection(new TagBlockItemOption()),
        new BlockItemOptionCollection(new MediaFolderBlockItemOption()),
        new BlockItemOptionCollection(new TextBlockItemOption()),
        new BlockItemOptionCollection(new LinkBlockItemOption()),
    ],
);
