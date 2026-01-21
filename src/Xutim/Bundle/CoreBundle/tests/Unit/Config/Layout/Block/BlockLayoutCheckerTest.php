<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Tests\Unit\Config\Layout\Block;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Uid\Uuid;
use Xutim\CoreBundle\Config\Layout\Block\BlockLayoutChecker;
use Xutim\CoreBundle\Config\Layout\Block\Option\ArticleBlockItemOption;
use Xutim\CoreBundle\Config\Layout\Block\Option\BlockItemOption;
use Xutim\CoreBundle\Config\Layout\Block\Option\BlockItemOptionCollection;
use Xutim\CoreBundle\Config\Layout\Block\Option\BlockItemOptionComposed;
use Xutim\CoreBundle\Config\Layout\Block\Option\ImageBlockItemOption;
use Xutim\CoreBundle\Config\Layout\Block\Option\LinkBlockItemOption;
use Xutim\CoreBundle\Config\Layout\Block\Option\MapCoordinatesBlockItemOption;
use Xutim\CoreBundle\Config\Layout\Block\Option\SnippetBlockItemOption;
use Xutim\CoreBundle\Config\Layout\Layout;
use Xutim\CoreBundle\Config\Layout\LayoutConfigItem;
use Xutim\CoreBundle\Entity\Block;
use Xutim\CoreBundle\Entity\BlockItem;
use Xutim\CoreBundle\Entity\File;
use Xutim\CoreBundle\Entity\Snippet;
use Xutim\CoreBundle\Infra\Layout\LayoutLoader;

class BlockLayoutCheckerTest extends TestCase
{
    /**
     * @param list<LayoutConfigItem> $config
     */
    private function getBlockLayoutCheckerForConfig(array $config): BlockLayoutChecker
    {
        $layout = new Layout('path', 'code', 'name', null, $config);

        $layoutLoaderStub = $this->createStub(LayoutLoader::class);
        $layoutLoaderStub->method('getBlockLayoutByCode')
            ->willReturn($layout);

        return new BlockLayoutChecker($layoutLoaderStub);
    }

    /**
     * @dataProvider configOptionsProvider
     * @param list<BlockItemOption> $config
     */
    public function testLayoutChecker(array $config, Block $block, bool $result): void
    {
        $checker = $this->getBlockLayoutCheckerForConfig($config);
        $this->assertEquals($result, $checker->checkLayout($block));
    }

    /**
     * @return array<array{0: array<BlockItemOption>, 1: Block, 2: bool}>
     */
    public function configOptionsProvider(): array
    {
        $block = new Block('code', 'name', 'desc', null, 'layout');
        $blockEmpty = new Block('code', 'name', 'desc', null, 'layout');
        $linkItem = new BlockItem($block, null, null, null, null, null, null, null, 'link');
        $linkItem2 = new BlockItem($block, null, null, null, null, null, null, null, 'link2');
        $linkItem3 = new BlockItem($block, null, null, null, null, null, null, null, 'link3');
        $coordItem = new BlockItem($block, null, null, null, null, null, null, null, null, null, null, 1.3, 2.4);
        $coordItem2 = new BlockItem($block, null, null, null, null, null, null, null, null, null, null, 5.3, 5.4);


        $block2 = new Block('code', 'name', 'desc', null, 'layout');
        $snippetItem = new BlockItem($block2, null, null, null, null, new Snippet('code'));
        $snippetItem = new BlockItem($block2, null, null, null, null, new Snippet('code'));
        $imageItem = new BlockItem($block2, null, null, null, new File(Uuid::v4(), 'name', 'alt', 'en', 'sldkfsdf.jpg', 'jpg', 'reference'));

        return [
            [[], $blockEmpty, true],
            [[new ArticleBlockItemOption()], $blockEmpty, false],
            [[new BlockItemOptionComposed(new ArticleBlockItemOption())], $blockEmpty, false],
            [[new BlockItemOptionCollection(new ArticleBlockItemOption())], $blockEmpty, false],
            [[new BlockItemOptionCollection(new LinkBlockItemOption()), new BlockItemOptionCollection(new MapCoordinatesBlockItemOption())], $block, true],
            [[new BlockItemOptionCollection(new LinkBlockItemOption()), new LinkBlockItemOption(), new BlockItemOptionCollection(new MapCoordinatesBlockItemOption()), new MapCoordinatesBlockItemOption()], $block, true],
            [[new BlockItemOptionCollection(new LinkBlockItemOption())], $block, false],
            [[new BlockItemOptionCollection(new LinkBlockItemOption()), new MapCoordinatesBlockItemOption()], $block, false],
            [[new SnippetBlockItemOption(), new SnippetBlockItemOption(), new ImageBlockItemOption()], $block2, true]
        ];
    }
}
