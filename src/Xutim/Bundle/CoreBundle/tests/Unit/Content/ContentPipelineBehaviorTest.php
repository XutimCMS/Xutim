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

final class ContentPipelineBehaviorTest extends TestCase
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

    public static function documentRoundTripProvider(): array
    {
        return [
            'empty document' => [
                'document' => ['time' => 10, 'version' => '2.31.0', 'blocks' => []],
                'assertion' => static function (TestCase $test, array $roundTrip): void {
                    $test->assertSame([], $roundTrip['blocks']);
                },
            ],
            'formatted paragraph and quote' => [
                'document' => [
                    'time' => 11,
                    'version' => '2.31.0',
                    'blocks' => [
                        [
                            'id' => 'p1',
                            'type' => 'paragraph',
                            'data' => ['text' => '<strong>Hello</strong> world'],
                            'tunes' => ['alignment' => ['alignment' => 'center']],
                        ],
                        [
                            'id' => 'q1',
                            'type' => 'quote',
                            'data' => ['text' => 'Body', 'caption' => '<em>Caption</em>', 'alignment' => 'center'],
                            'tunes' => [],
                        ],
                    ],
                ],
                'assertion' => static function (TestCase $test, array $roundTrip): void {
                    $test->assertSame('<strong>Hello</strong> world', $roundTrip['blocks'][0]['data']['text']);
                    $test->assertSame('center', $roundTrip['blocks'][0]['tunes']['alignment']['alignment']);
                    $test->assertSame('<em>Caption</em>', $roundTrip['blocks'][1]['data']['caption']);
                },
            ],
            'checklist preserves nested structure' => [
                'document' => [
                    'blocks' => [[
                        'id' => 'list-1',
                        'type' => 'list',
                        'data' => [
                            'style' => 'checklist',
                            'meta' => ['start' => 1, 'counterType' => 'numeric'],
                            'items' => [[
                                'content' => 'Top',
                                'meta' => '',
                                'items' => [[
                                    'content' => 'Child',
                                    'meta' => '',
                                    'items' => [[
                                        'content' => 'Grandchild',
                                        'meta' => '',
                                        'items' => [],
                                    ]],
                                ]],
                            ]],
                        ],
                        'tunes' => [],
                    ]],
                ],
                'assertion' => static function (TestCase $test, array $roundTrip): void {
                    $test->assertSame('checklist', $roundTrip['blocks'][0]['data']['style']);
                    $test->assertSame('Grandchild', $roundTrip['blocks'][0]['data']['items'][0]['items'][0]['items'][0]['content']);
                },
            ],
            'unknown block survives unchanged' => [
                'document' => [
                    'blocks' => [[
                        'id' => 'u1',
                        'type' => 'customWidget',
                        'data' => ['foo' => 'bar', 'deep' => ['x' => 1]],
                        'tunes' => ['widgetTune' => ['mode' => 'compact']],
                    ]],
                ],
                'assertion' => static function (TestCase $test, array $roundTrip): void {
                    $test->assertSame('customWidget', $roundTrip['blocks'][0]['type']);
                    $test->assertSame(['foo' => 'bar', 'deep' => ['x' => 1]], $roundTrip['blocks'][0]['data']);
                    $test->assertSame(['widgetTune' => ['mode' => 'compact']], $roundTrip['blocks'][0]['tunes']);
                },
            ],
        ];
    }

    #[DataProvider('documentRoundTripProvider')]
    public function test_document_round_trip_behaves_as_expected(array $document, callable $assertion): void
    {
        $canonical = $this->transformer->transform($document);
        $roundTrip = $this->adapter->toEditorJsDocument($canonical);

        $assertion($this, $roundTrip);
    }

    public static function extractorProvider(): array
    {
        return [
            'extract introduction across paragraph quote and list' => [
                'document' => [
                    'blocks' => [
                        ['id' => 'p1', 'type' => 'paragraph', 'data' => ['text' => 'Alpha'], 'tunes' => []],
                        ['id' => 'q1', 'type' => 'quote', 'data' => ['text' => 'Beta', 'caption' => 'Gamma', 'alignment' => 'center'], 'tunes' => []],
                        ['id' => 'l1', 'type' => 'list', 'data' => [
                            'style' => 'unordered',
                            'meta' => ['start' => 1, 'counterType' => 'numeric'],
                            'items' => [['content' => 'Delta', 'meta' => '', 'items' => []]],
                        ], 'tunes' => []],
                    ],
                ],
                'assertion' => static function (TestCase $test, CanonicalContentExtractor $extractor, mixed $document): void {
                    $test->assertSame('Alpha Beta Delta ', $extractor->extractIntroduction($document));
                },
            ],
            'extract first paragraph html only' => [
                'document' => [
                    'blocks' => [
                        ['id' => 'p1', 'type' => 'paragraph', 'data' => ['text' => '<em>First</em>'], 'tunes' => []],
                        ['id' => 'p2', 'type' => 'paragraph', 'data' => ['text' => 'Second'], 'tunes' => []],
                    ],
                ],
                'assertion' => static function (TestCase $test, CanonicalContentExtractor $extractor, mixed $document): void {
                    $test->assertSame('<p><em>First</em></p>', $extractor->extractParagraphsHtml($document, 1));
                },
            ],
            'extract timeline throws on incomplete trailing pair' => [
                'document' => [
                    'blocks' => [
                        ['id' => 'h1', 'type' => 'header', 'data' => ['text' => 'Year 1', 'level' => 2], 'tunes' => []],
                        ['id' => 'p1', 'type' => 'paragraph', 'data' => ['text' => 'Event 1'], 'tunes' => []],
                        ['id' => 'h2', 'type' => 'header', 'data' => ['text' => 'Year 2', 'level' => 2], 'tunes' => []],
                    ],
                ],
                'assertion' => static function (TestCase $test, CanonicalContentExtractor $extractor, mixed $document): void {
                    $test->expectException(\UnexpectedValueException::class);
                    $extractor->extractTimelineElements($document);
                },
            ],
        ];
    }

    #[DataProvider('extractorProvider')]
    public function test_extractors_behave_as_expected(array $document, callable $assertion): void
    {
        $canonical = $this->transformer->transform($document);

        $assertion($this, $this->extractor, $canonical);
    }

    public static function diffProvider(): array
    {
        return [
            'formatting only paragraph change' => [
                'old' => [
                    'blocks' => [[
                        'id' => 'p1',
                        'type' => 'paragraph',
                        'data' => ['text' => '<strong>Hello</strong> world'],
                        'tunes' => [],
                    ]],
                ],
                'new' => [
                    'blocks' => [[
                        'id' => 'p1',
                        'type' => 'paragraph',
                        'data' => ['text' => '<em>Hello</em> world'],
                        'tunes' => [],
                    ]],
                ],
                'assertion' => static function (TestCase $test, array $rows): void {
                    $test->assertSame('modified_text', $rows[0]['op']);
                    $test->assertStringContainsString('<del>', $rows[0]['body_html']);
                    $test->assertStringContainsString('<ins>', $rows[0]['body_html']);
                },
            ],
            'quote caption only change' => [
                'old' => [
                    'blocks' => [[
                        'id' => 'q1',
                        'type' => 'quote',
                        'data' => ['text' => 'Body', 'caption' => 'Old', 'alignment' => 'center'],
                        'tunes' => [],
                    ]],
                ],
                'new' => [
                    'blocks' => [[
                        'id' => 'q1',
                        'type' => 'quote',
                        'data' => ['text' => 'Body', 'caption' => 'New', 'alignment' => 'center'],
                        'tunes' => [],
                    ]],
                ],
                'assertion' => static function (TestCase $test, array $rows): void {
                    $test->assertSame('modified', $rows[0]['op']);
                    $test->assertSame('same', $rows[0]['meta']['text']['status']);
                    $test->assertSame('changed', $rows[0]['meta']['caption']['status']);
                },
            ],
            'nested list child change is visible' => [
                'old' => [
                    'blocks' => [[
                        'id' => 'l1',
                        'type' => 'list',
                        'data' => [
                            'style' => 'unordered',
                            'meta' => ['start' => 1, 'counterType' => 'numeric'],
                            'items' => [[
                                'content' => 'Parent',
                                'meta' => '',
                                'items' => [[
                                    'content' => 'Child one',
                                    'meta' => '',
                                    'items' => [],
                                ]],
                            ]],
                        ],
                        'tunes' => [],
                    ]],
                ],
                'new' => [
                    'blocks' => [[
                        'id' => 'l1',
                        'type' => 'list',
                        'data' => [
                            'style' => 'unordered',
                            'meta' => ['start' => 1, 'counterType' => 'numeric'],
                            'items' => [[
                                'content' => 'Parent',
                                'meta' => '',
                                'items' => [[
                                    'content' => 'Child two',
                                    'meta' => '',
                                    'items' => [],
                                ]],
                            ]],
                        ],
                        'tunes' => [],
                    ]],
                ],
                'assertion' => static function (TestCase $test, array $rows): void {
                    $test->assertSame('modified_text', $rows[0]['op']);
                    $test->assertStringContainsString('Child', $rows[0]['body_html']);
                },
            ],
            'middle insertion stays in order' => [
                'old' => [
                    'blocks' => [
                        ['id' => 'a', 'type' => 'paragraph', 'data' => ['text' => 'A'], 'tunes' => []],
                        ['id' => 'c', 'type' => 'paragraph', 'data' => ['text' => 'C'], 'tunes' => []],
                    ],
                ],
                'new' => [
                    'blocks' => [
                        ['id' => 'a', 'type' => 'paragraph', 'data' => ['text' => 'A'], 'tunes' => []],
                        ['id' => 'b', 'type' => 'paragraph', 'data' => ['text' => 'B'], 'tunes' => []],
                        ['id' => 'c', 'type' => 'paragraph', 'data' => ['text' => 'C'], 'tunes' => []],
                    ],
                ],
                'assertion' => static function (TestCase $test, array $rows): void {
                    $test->assertSame('a', $rows[0]['render_key']);
                    $test->assertSame('added', $rows[1]['op']);
                    $test->assertSame('b', $rows[1]['render_key']);
                    $test->assertSame('c', $rows[2]['render_key']);
                },
            ],
            'foldable attribute change is semantic not text change' => [
                'old' => [
                    'blocks' => [
                        ['id' => 'f1', 'type' => 'foldableStart', 'data' => ['title' => 'Section', 'open' => false], 'tunes' => []],
                        ['id' => 'f1e', 'type' => 'foldableEnd', 'data' => [], 'tunes' => []],
                    ],
                ],
                'new' => [
                    'blocks' => [
                        ['id' => 'f1', 'type' => 'foldableStart', 'data' => ['title' => 'Section', 'open' => true], 'tunes' => []],
                        ['id' => 'f1e', 'type' => 'foldableEnd', 'data' => [], 'tunes' => []],
                    ],
                ],
                'assertion' => static function (TestCase $test, array $rows): void {
                    $test->assertSame('modified', $rows[0]['op']);
                    $test->assertStringContainsString('Section', $rows[0]['body_html']);
                },
            ],
        ];
    }

    #[DataProvider('diffProvider')]
    public function test_diff_pipeline_behaves_as_expected(array $old, array $new, callable $assertion): void
    {
        $oldCanonical = $this->transformer->transform($old);
        $newCanonical = $this->transformer->transform($new);
        $rows = $this->diffRenderer->diffDocuments($oldCanonical, $newCanonical);

        $assertion($this, $rows);
    }

    public static function diffTextProvider(): array
    {
        return [
            'same text stays plain' => ['old' => 'Hello world', 'new' => 'Hello world', 'contains' => ['Hello world'], 'notContains' => ['<ins>', '<del>']],
            'inserted words are grouped' => ['old' => 'Hello world', 'new' => 'Hello brave new world', 'contains' => ['<ins>brave new </ins>'], 'notContains' => []],
            'deleted words are grouped' => ['old' => 'Hello brave new world', 'new' => 'Hello world', 'contains' => ['<del>brave new </del>'], 'notContains' => []],
            'line breaks are preserved as br' => ['old' => "One\nTwo", 'new' => "One\nThree", 'contains' => ['<br>', 'Three'], 'notContains' => []],
        ];
    }

    #[DataProvider('diffTextProvider')]
    public function test_diff_text_behaves_as_expected(string $old, string $new, array $contains, array $notContains): void
    {
        $html = $this->diffRenderer->diffText($old, $new);

        foreach ($contains as $needle) {
            $this->assertStringContainsString($needle, $html);
        }

        foreach ($notContains as $needle) {
            $this->assertStringNotContainsString($needle, $html);
        }
    }
}
