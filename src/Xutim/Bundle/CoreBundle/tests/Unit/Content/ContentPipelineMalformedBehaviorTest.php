<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Tests\Unit\Content;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Xutim\CoreBundle\Content\Adapter\CanonicalEditorJsAdapter;
use Xutim\CoreBundle\Content\CanonicalContentExtractor;
use Xutim\CoreBundle\Content\Diff\CanonicalContentDiffRenderer;
use Xutim\CoreBundle\Content\Diff\InlineDiffRenderer;
use Xutim\CoreBundle\Content\Transform\EditorJsToCanonicalDocumentTransformer;
use Xutim\CoreBundle\Content\Transform\InlineHtmlParser;

final class ContentPipelineMalformedBehaviorTest extends TestCase
{
    private EditorJsToCanonicalDocumentTransformer $transformer;
    private CanonicalEditorJsAdapter $adapter;
    private CanonicalContentExtractor $extractor;
    private CanonicalContentDiffRenderer $diffRenderer;

    protected function setUp(): void
    {
        $this->transformer = new EditorJsToCanonicalDocumentTransformer(new InlineHtmlParser());
        $this->adapter = new CanonicalEditorJsAdapter();
        $this->extractor = new CanonicalContentExtractor($this->adapter);
        $this->diffRenderer = new CanonicalContentDiffRenderer(
            $this->extractor,
            new InlineDiffRenderer(),
        );
    }

    public static function malformedDocumentProvider(): array
    {
        return [
            'missing blocks key' => [
                'document' => ['time' => 1, 'version' => '2.31.0'],
            ],
            'blocks is wrong type' => [
                'document' => ['blocks' => 'not-an-array'],
            ],
            'block missing type and data' => [
                'document' => ['blocks' => [['id' => 'x1']]],
            ],
            'orphan foldable end' => [
                'document' => ['blocks' => [[
                    'id' => 'end-1',
                    'type' => 'foldableEnd',
                    'data' => [],
                    'tunes' => [],
                ]]],
            ],
            'paragraph with invalid data shape' => [
                'document' => ['blocks' => [[
                    'id' => 'p1',
                    'type' => 'paragraph',
                    'data' => 'wrong',
                    'tunes' => [],
                ]]],
            ],
            'list with invalid items payload' => [
                'document' => ['blocks' => [[
                    'id' => 'l1',
                    'type' => 'list',
                    'data' => [
                        'style' => 'unordered',
                        'meta' => ['start' => 1, 'counterType' => 'numeric'],
                        'items' => 'wrong',
                    ],
                    'tunes' => [],
                ]]],
            ],
            'quote with null caption' => [
                'document' => ['blocks' => [[
                    'id' => 'q1',
                    'type' => 'quote',
                    'data' => ['text' => 'Body', 'caption' => null, 'alignment' => 'center'],
                    'tunes' => [],
                ]]],
            ],
        ];
    }

    #[DataProvider('malformedDocumentProvider')]
    public function test_malformed_documents_degrade_safely(array $document): void
    {
        $canonical = $this->transformer->transform($document);
        $roundTrip = $this->adapter->toEditorJsDocument($canonical);
        $rows = $this->diffRenderer->diffDocuments($canonical, $canonical);

        $this->assertIsArray($canonical->blocks);
        $this->assertIsArray($roundTrip['blocks']);
        $this->assertIsArray($rows);
    }

    public function test_unknown_markup_is_preserved_without_breaking_pipeline(): void
    {
        $document = [
            'blocks' => [[
                'id' => 'p1',
                'type' => 'paragraph',
                'data' => ['text' => '<span class="unknown">Hello</span> world'],
                'tunes' => [],
            ]],
        ];

        $canonical = $this->transformer->transform($document);
        $roundTrip = $this->adapter->toEditorJsDocument($canonical);
        $rows = $this->diffRenderer->diffDocuments($canonical, $canonical);

        $this->assertCount(1, $canonical->blocks);
        $this->assertNotNull($canonical->blocks[0]->fallbackRaw);
        $this->assertSame('Hello world', $this->extractor->runsToPlainText($canonical->blocks[0]->body));
        $this->assertNotEmpty($roundTrip['blocks']);
        $this->assertSame('unchanged', $rows[0]['op']);
    }
}
