<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Tests\Unit\Service;

use PHPUnit\Framework\TestCase;
use Twig\Environment;
use Xutim\CoreBundle\Config\Section\SectionDefinitionRegistry;
use Xutim\CoreBundle\Content\Adapter\CanonicalEditorJsAdapter;
use Xutim\CoreBundle\Content\CanonicalContentExtractor;
use Xutim\CoreBundle\Content\Diff\CanonicalContentDiffRenderer;
use Xutim\CoreBundle\Content\Diff\InlineDiffRenderer;
use Xutim\CoreBundle\Content\Transform\EditorJsToCanonicalDocumentTransformer;
use Xutim\CoreBundle\Content\Transform\InlineHtmlParser;
use Xutim\CoreBundle\Service\ContentFragmentsConverter;
use Xutim\CoreBundle\Service\EditorJsDiffRenderer;
use Xutim\MediaBundle\Repository\MediaRepositoryInterface;

final class ContentFragmentsConverterTest extends TestCase
{
    public function testExtractCopyrightsSkipsImageBlockWithoutFile(): void
    {
        $mediaRepo = $this->createMock(MediaRepositoryInterface::class);
        $mediaRepo->expects(self::never())->method('findById');

        $converter = $this->createConverter($this->createStub(Environment::class), $mediaRepo);

        $copyrights = $converter->extractCopyrights([
            'time' => 1,
            'version' => '2.0',
            'blocks' => [[
                'id' => 'image-block',
                'type' => 'xutimImage',
                'data' => ['file' => ['url' => '', 'id' => '', 'thumbnailUrl' => '']],
                'tunes' => ['xutimAnchor' => ['anchor' => '']],
            ]],
        ]);

        self::assertSame([], $copyrights);
    }

    public function testAdminHtmlRendersEveryBlockAsAnUnchangedDiffRow(): void
    {
        $twig = $this->createMock(Environment::class);
        $twig->expects(self::once())
            ->method('render')
            ->with(
                '@XutimCore/admin/content_fragment/content_diff.html.twig',
                self::callback(static function (array $context): bool {
                    $rows = $context['rows'];
                    $kinds = array_column($rows, 'kind');
                    $ops = array_unique(array_column($rows, 'op'));

                    return $context['locale'] === 'en'
                        && $kinds === ['paragraph', 'list']
                        && $ops === ['unchanged']
                        && $rows[1]['attrs']['style'] === 'ordered'
                        && $rows[1]['items'][0]['html'] === 'First';
                }),
            )
            ->willReturn('<p>rendered</p>');

        $converter = $this->createConverter($twig, $this->createStub(MediaRepositoryInterface::class));

        $html = $converter->convertToAdminHtml([
            'time' => 1,
            'version' => '2.0',
            'blocks' => [
                ['id' => 'p', 'type' => 'paragraph', 'data' => ['text' => 'Hello']],
                ['id' => 'l', 'type' => 'list', 'data' => [
                    'style' => 'ordered',
                    'meta' => [],
                    'items' => [['content' => 'First', 'meta' => [], 'items' => []]],
                ]],
            ],
        ], 'en');

        self::assertSame('<p>rendered</p>', $html);
    }

    private function createConverter(Environment $twig, MediaRepositoryInterface $mediaRepo): ContentFragmentsConverter
    {
        $transformer = new EditorJsToCanonicalDocumentTransformer(new InlineHtmlParser());
        $extractor = new CanonicalContentExtractor(new CanonicalEditorJsAdapter());
        $sectionRegistry = new SectionDefinitionRegistry([]);

        return new ContentFragmentsConverter(
            $twig,
            $mediaRepo,
            $transformer,
            $extractor,
            new EditorJsDiffRenderer(
                $transformer,
                new CanonicalContentDiffRenderer($extractor, new InlineDiffRenderer(), $sectionRegistry),
                $sectionRegistry,
            ),
        );
    }
}
