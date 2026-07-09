<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Tests\Unit\Twig;

use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Twig\Environment;
use Xutim\CoreBundle\Config\Layout\Block\Option\BlockItemOption;
use Xutim\CoreBundle\Config\Layout\Block\Option\ImageBlockItemOption;
use Xutim\CoreBundle\Config\Layout\Block\Option\LinkBlockItemOption;
use Xutim\CoreBundle\Config\Layout\Block\Option\PageBlockItemOption;
use Xutim\CoreBundle\Config\Layout\Block\Option\TextBlockItemOption;
use Xutim\CoreBundle\Config\Layout\Definition\LayoutDefinition;
use Xutim\CoreBundle\Config\Layout\Definition\LayoutDefinitionRegistry;
use Xutim\CoreBundle\Context\SiteContext;
use Xutim\CoreBundle\Domain\Model\ContentTranslationInterface;
use Xutim\CoreBundle\Domain\Model\PageInterface;
use Xutim\CoreBundle\Repository\ArticleRepository;
use Xutim\CoreBundle\Repository\PageRepository;
use Xutim\CoreBundle\Repository\TagRepository;
use Xutim\CoreBundle\Service\AdminEditUrl\AdminEditUrlResolver;
use Xutim\CoreBundle\Service\XutimLayoutValueResolver;
use Xutim\CoreBundle\Twig\Runtime\XutimLayoutDiffExtensionRuntime;
use Xutim\CoreBundle\Twig\Runtime\XutimLayoutExtensionRuntime;
use Xutim\MediaBundle\Domain\Model\MediaInterface;
use Xutim\MediaBundle\Repository\MediaFolderRepositoryInterface;
use Xutim\MediaBundle\Repository\MediaRepositoryInterface;
use Xutim\SnippetBundle\Domain\Repository\SnippetRepositoryInterface;

final class XutimLayoutDiffExtensionRuntimeTest extends TestCase
{
    private const PAGE_ID = '784305be-175d-4015-bf70-ce60ef40d34b';
    private const MEDIA_ID = 'e39c3d40-0efd-4cbf-9f8a-eb343b4c05f8';

    public function testLayoutNameFallsBackToCode(): void
    {
        $runtime = $this->createRuntime([]);

        self::assertSame('unknown-code', $runtime->layoutName('unknown-code'));
    }

    public function testLayoutNameUsesDefinitionName(): void
    {
        $runtime = $this->createRuntime(['hero' => new TextBlockItemOption()]);

        self::assertSame('Test', $runtime->layoutName('test'));
    }

    public function testEmptyValueDisplaysAsEmpty(): void
    {
        $runtime = $this->createRuntime(['title' => new TextBlockItemOption()]);

        self::assertSame(['kind' => 'empty', 'text' => ''], $runtime->fieldDisplay('test', 'title', '', 'en'));
        self::assertSame(['kind' => 'empty', 'text' => ''], $runtime->fieldDisplay('test', 'title', null, 'en'));
    }

    public function testScalarValueDisplaysAsText(): void
    {
        $runtime = $this->createRuntime(['color' => new TextBlockItemOption()]);

        self::assertSame(['kind' => 'text', 'text' => 'blue'], $runtime->fieldDisplay('test', 'color', 'blue', 'en'));
    }

    public function testUnknownLayoutFallsBackToScalarDisplay(): void
    {
        $runtime = $this->createRuntime([]);

        self::assertSame(['kind' => 'text', 'text' => 'blue'], $runtime->fieldDisplay('gone', 'color', 'blue', 'en'));
    }

    public function testHttpUrlDisplaysAsUrl(): void
    {
        $runtime = $this->createRuntime(['link' => new LinkBlockItemOption()]);

        $display = $runtime->fieldDisplay('test', 'link', 'https://example.com/x', 'en');

        self::assertSame('url', $display['kind']);
        self::assertSame('https://example.com/x', $display['text']);
    }

    public function testPageRefResolvesToEntityDisplay(): void
    {
        $translation = $this->createStub(ContentTranslationInterface::class);
        $translation->method('getTitle')->willReturn('Becoming a brother');
        $page = $this->createStub(PageInterface::class);
        $page->method('getTranslationByLocaleOrAny')->willReturn($translation);

        $pageRepository = $this->createStub(PageRepository::class);
        $pageRepository->method('find')->willReturn($page);

        $runtime = $this->createRuntime(['page1' => new PageBlockItemOption()], pageRepository: $pageRepository);

        $display = $runtime->fieldDisplay('test', 'page1', self::PAGE_ID, 'en');

        self::assertSame('entity', $display['kind']);
        self::assertSame('Becoming a brother', $display['text']);
        self::assertSame('Page', $display['typeLabel']);
    }

    public function testUnresolvedPageRefDisplaysAsMissing(): void
    {
        $runtime = $this->createRuntime(['page1' => new PageBlockItemOption()]);

        $display = $runtime->fieldDisplay('test', 'page1', self::PAGE_ID, 'en');

        self::assertSame('missing', $display['kind']);
        self::assertSame(self::PAGE_ID, $display['text']);
    }

    public function testImageRefResolvesToImageDisplay(): void
    {
        $media = $this->createStub(MediaInterface::class);
        $media->method('isImage')->willReturn(true);
        $media->method('innerName')->willReturn('brothers.jpg');

        $mediaRepository = $this->createStub(MediaRepositoryInterface::class);
        $mediaRepository->method('findById')->willReturn($media);

        $runtime = $this->createRuntime(['image' => new ImageBlockItemOption()], mediaRepository: $mediaRepository);

        $display = $runtime->fieldDisplay('test', 'image', self::MEDIA_ID, 'en');

        self::assertSame('image', $display['kind']);
        self::assertSame('brothers.jpg', $display['text']);
        self::assertSame($media, $display['media']);
    }

    /**
     * @param array<string, BlockItemOption> $fields
     */
    private function createRuntime(
        array $fields,
        ?PageRepository $pageRepository = null,
        ?MediaRepositoryInterface $mediaRepository = null,
    ): XutimLayoutDiffExtensionRuntime {
        $logger = $this->createStub(LoggerInterface::class);
        $registry = new LayoutDefinitionRegistry($fields === [] ? [] : [$this->layoutWithFields($fields)]);
        $resolver = new XutimLayoutValueResolver(
            $mediaRepository ?? $this->createStub(MediaRepositoryInterface::class),
            $this->createStub(MediaFolderRepositoryInterface::class),
            $pageRepository ?? $this->createStub(PageRepository::class),
            $this->createStub(ArticleRepository::class),
            $this->createStub(TagRepository::class),
            $this->createStub(SnippetRepositoryInterface::class),
            $logger,
        );
        $adminEditUrlResolver = new AdminEditUrlResolver([]);

        return new XutimLayoutDiffExtensionRuntime(
            $registry,
            $resolver,
            $adminEditUrlResolver,
            new XutimLayoutExtensionRuntime(
                $registry,
                $resolver,
                $this->createStub(Environment::class),
                $logger,
                $adminEditUrlResolver,
            ),
            $this->createStub(SiteContext::class),
        );
    }

    /**
     * @param array<string, BlockItemOption> $fields
     */
    private function layoutWithFields(array $fields): LayoutDefinition
    {
        return new class($fields) implements LayoutDefinition {
            /**
             * @param array<string, BlockItemOption> $fields
             */
            public function __construct(private readonly array $fields)
            {
            }

            public function getCode(): string
            {
                return 'test';
            }

            public function getName(): string
            {
                return 'Test';
            }

            public function getFields(): array
            {
                return $this->fields;
            }

            public function getFieldDescriptions(): array
            {
                return [];
            }

            public function getTemplate(): string
            {
                return 'test.html.twig';
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
                return '';
            }

            public function getPreviewImage(): string
            {
                return '';
            }
        };
    }
}
