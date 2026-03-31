<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Tests\Unit\Content;

use PHPUnit\Framework\TestCase;
use Xutim\CoreBundle\Content\Adapter\CanonicalEditorJsAdapter;
use Xutim\CoreBundle\Content\Transform\EditorJsToCanonicalDocumentTransformer;
use Xutim\CoreBundle\Content\Transform\InlineHtmlParser;

final class CanonicalEditorJsAdapterTest extends TestCase
{
    private CanonicalEditorJsAdapter $adapter;
    private EditorJsToCanonicalDocumentTransformer $transformer;

    protected function setUp(): void
    {
        $this->adapter = new CanonicalEditorJsAdapter();
        $this->transformer = new EditorJsToCanonicalDocumentTransformer(new InlineHtmlParser());
    }

    public function test_round_trip_preserves_nested_lists(): void
    {
        $document = [
            'time' => 123,
            'version' => '2.31.0',
            'blocks' => [[
                'id' => 'list-1',
                'type' => 'list',
                'data' => [
                    'style' => 'unordered',
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
        ];

        $canonical = $this->transformer->transform($document);
        $roundTrip = $this->adapter->toEditorJsDocument($canonical);

        $children = $roundTrip['blocks'][0]['data']['items'][0]['items'];

        self::assertSame('Top', $roundTrip['blocks'][0]['data']['items'][0]['content']);
        self::assertSame('Child', $children[0]['content']);
        self::assertSame('Grandchild', $children[0]['items'][0]['content']);
    }

    public function test_round_trip_preserves_unknown_block_fallback(): void
    {
        $document = [
            'blocks' => [[
                'id' => 'unknown-1',
                'type' => 'customThing',
                'data' => ['foo' => 'bar'],
                'tunes' => ['x' => ['y' => 'z']],
            ]],
        ];

        $canonical = $this->transformer->transform($document);
        $roundTrip = $this->adapter->toEditorJsDocument($canonical);

        self::assertSame($document['blocks'][0], $roundTrip['blocks'][0]);
    }
}
