<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Tests\Unit\Config\Layout\Block;

use PHPUnit\Framework\Attributes\DataProvider;
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
use Xutim\CoreBundle\Infra\Layout\LayoutLoader;
use Xutim\MediaBundle\Domain\Model\MediaInterface;
use Xutim\SnippetBundle\Domain\Model\Snippet;
use Xutim\SnippetBundle\Domain\Model\SnippetCategory;

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
     * @param list<BlockItemOption> $config
     */
    #[DataProvider('configOptionsProvider')]
    public function testLayoutChecker(array $config, Block $block, bool $result): void
    {
        $checker = $this->getBlockLayoutCheckerForConfig($config);
        $this->assertEquals($result, $checker->checkLayout($block));
    }

    /**
     * @return array<array{0: array<BlockItemOption>, 1: Block, 2: bool}>
     */
    public static function configOptionsProvider(): array
    {
        $block = new Block('code', 'name', 'desc', null, 'layout');
        $blockEmpty = new Block('code', 'name', 'desc', null, 'layout');
        $linkItem = new BlockItem($block, null, null, null, null, null, null, null, 'link');
        $linkItem2 = new BlockItem($block, null, null, null, null, null, null, null, 'link2');
        $linkItem3 = new BlockItem($block, null, null, null, null, null, null, null, 'link3');
        $coordItem = new BlockItem($block, null, null, null, null, null, null, null, null, null, null, 1.3, 2.4);
        $coordItem2 = new BlockItem($block, null, null, null, null, null, null, null, null, null, null, 5.3, 5.4);


        $block2 = new Block('code', 'name', 'desc', null, 'layout');
        $snippetItem = new BlockItem($block2, null, null, null, new Snippet('code', 'Test snippet', SnippetCategory::Ui));
        $snippetItem = new BlockItem($block2, null, null, null, new Snippet('code2', 'Test snippet 2', SnippetCategory::Ui));
        $media = new class() implements MediaInterface {
            public function id(): Uuid
            {
                return Uuid::v4();
            }
            public function folder(): ?\Xutim\MediaBundle\Domain\Model\MediaFolderInterface
            {
                return null;
            }
            public function originalPath(): string
            {
                return '/path/to/file.jpg';
            }
            public function originalExt(): string
            {
                return 'jpg';
            }
            public function mime(): string
            {
                return 'image/jpeg';
            }
            public function hash(): string
            {
                return 'hash123';
            }
            public function sizeBytes(): int
            {
                return 0;
            }
            public function width(): int
            {
                return 0;
            }
            public function height(): int
            {
                return 0;
            }
            public function copyright(): ?string
            {
                return 'Copyright 2024';
            }
            public function focalX(): ?float
            {
                return null;
            }
            public function focalY(): ?float
            {
                return null;
            }
            public function blurHash(): ?string
            {
                return null;
            }
            public function isImage(): bool
            {
                return true;
            }
            public function getCreatedAt(): \DateTimeImmutable
            {
                return new \DateTimeImmutable();
            }
            public function getUpdatedAt(): \DateTimeImmutable
            {
                return new \DateTimeImmutable();
            }
            public function change(?string $copyright, ?float $focalX, ?float $focalY, ?\Xutim\MediaBundle\Domain\Model\MediaFolderInterface $folder, ?string $blurHash): void
            {
            }
            public function changeFolder(?\Xutim\MediaBundle\Domain\Model\MediaFolderInterface $folder): void
            {
            }
            public function innerName(): string
            {
                return 'test';
            }
            public function changeInnerName(string $innerName): void
            {
            }
            public function changeCopyright(string $copyright): void
            {
            }
            public function changeBlurHash(string $blurHash): void
            {
            }
            public function replaceFile(string $mime, string $hash, int $sizeBytes, int $width, int $height): void
            {
            }
            public function getTranslationByLocale(string $locale): ?\Xutim\MediaBundle\Domain\Model\MediaTranslationInterface
            {
                return null;
            }
            public function addTranslation(\Xutim\MediaBundle\Domain\Model\MediaTranslationInterface $translation): void
            {
            }
            public function getTranslations(): \Doctrine\Common\Collections\Collection
            {
                return new \Doctrine\Common\Collections\ArrayCollection();
            }
        };
        $imageItem = new BlockItem($block2, null, null, $media);

        return [
            [[], $blockEmpty, true],
            [[new ArticleBlockItemOption()], $blockEmpty, false],
            [[new BlockItemOptionComposed(new ArticleBlockItemOption())], $blockEmpty, false],
            [[new BlockItemOptionCollection(new ArticleBlockItemOption())], $blockEmpty, true], // Collections allow 0 or more items
            [[new BlockItemOptionCollection(new LinkBlockItemOption()), new BlockItemOptionCollection(new MapCoordinatesBlockItemOption())], $block, true],
            [[new BlockItemOptionCollection(new LinkBlockItemOption()), new LinkBlockItemOption(), new BlockItemOptionCollection(new MapCoordinatesBlockItemOption()), new MapCoordinatesBlockItemOption()], $block, true],
            [[new BlockItemOptionCollection(new LinkBlockItemOption())], $block, false],
            [[new BlockItemOptionCollection(new LinkBlockItemOption()), new MapCoordinatesBlockItemOption()], $block, false],
            [[new SnippetBlockItemOption(), new SnippetBlockItemOption(), new ImageBlockItemOption()], $block2, true]
        ];
    }
}
