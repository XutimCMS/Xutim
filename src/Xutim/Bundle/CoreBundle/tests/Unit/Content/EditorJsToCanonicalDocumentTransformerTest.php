<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Tests\Unit\Content;

use PHPUnit\Framework\TestCase;
use Xutim\CoreBundle\Content\Transform\EditorJsToCanonicalDocumentTransformer;
use Xutim\CoreBundle\Content\Transform\InlineHtmlParser;

final class EditorJsToCanonicalDocumentTransformerTest extends TestCase
{
    private EditorJsToCanonicalDocumentTransformer $transformer;

    protected function setUp(): void
    {
        $this->transformer = new EditorJsToCanonicalDocumentTransformer(new InlineHtmlParser());
    }

    public function test_transform_maps_blocks_and_foldables(): void
    {
        $document = [
            'time' => 123,
            'version' => '2.31.0',
            'blocks' => [
                [
                    'id' => 'p1',
                    'type' => 'paragraph',
                    'data' => ['text' => '<em>Hello</em> world'],
                    'tunes' => ['alignment' => ['alignment' => 'center']],
                ],
                [
                    'id' => 'fold-start',
                    'type' => 'foldableStart',
                    'data' => ['title' => 'More', 'open' => true],
                    'tunes' => [],
                ],
                [
                    'id' => 'h1',
                    'type' => 'header',
                    'data' => ['text' => 'Nested heading', 'level' => 2],
                    'tunes' => ['xutimAnchor' => ['anchor' => 'inside']],
                ],
                [
                    'id' => 'fold-end',
                    'type' => 'foldableEnd',
                    'data' => [],
                    'tunes' => [],
                ],
                [
                    'id' => 'q1',
                    'type' => 'quote',
                    'data' => ['text' => 'Body', 'caption' => '<br>', 'alignment' => 'center'],
                    'tunes' => [],
                ],
            ],
        ];

        $canonical = $this->transformer->transform($document);

        self::assertSame('editorjs', $canonical->meta['source']);
        self::assertCount(3, $canonical->blocks);
        self::assertSame('paragraph', $canonical->blocks[0]->kind);
        self::assertSame('center', $canonical->blocks[0]->attrs['align']);
        self::assertSame('italic', $canonical->blocks[0]->body[0]->marks[0]->type);
        self::assertSame(' world', $canonical->blocks[0]->body[1]->text);

        self::assertSame('foldable', $canonical->blocks[1]->kind);
        self::assertTrue($canonical->blocks[1]->attrs['open']);
        self::assertCount(1, $canonical->blocks[1]->children);
        self::assertSame('heading', $canonical->blocks[1]->children[0]->kind);
        self::assertSame('inside', $canonical->blocks[1]->children[0]->attrs['anchor']);

        self::assertSame('quote', $canonical->blocks[2]->kind);
        self::assertSame([], $canonical->blocks[2]->parts['caption']);
        self::assertSame('center', $canonical->blocks[2]->attrs['align']);
    }

    public function test_transform_preserves_unknown_block_losslessly(): void
    {
        $document = [
            'blocks' => [[
                'id' => 'x1',
                'type' => 'customThing',
                'data' => ['foo' => 'bar'],
                'tunes' => [],
            ]],
        ];

        $canonical = $this->transformer->transform($document);

        self::assertCount(1, $canonical->blocks);
        self::assertSame('unknown', $canonical->blocks[0]->kind);
        self::assertSame('customThing', $canonical->blocks[0]->attrs['sourceType']);
        self::assertSame('bar', $canonical->blocks[0]->fallbackRaw['data']['foo']);
    }

    public function test_transform_list_propagates_unsupported_markup_and_recurses_children(): void
    {
        $document = [
            'blocks' => [[
                'id' => 'list-1',
                'type' => 'list',
                'data' => [
                    'style' => 'unordered',
                    'meta' => ['start' => 1, 'counterType' => 'numeric'],
                    'items' => [[
                        'content' => 'Top <span class="x">item</span>',
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

        self::assertCount(1, $canonical->blocks);
        self::assertSame('list', $canonical->blocks[0]->kind);
        self::assertNotNull($canonical->blocks[0]->fallbackRaw);
        self::assertSame('Top item', $canonical->blocks[0]->listItems[0]->body[0]->text);
        self::assertSame('Child', $canonical->blocks[0]->listItems[0]->children[0]->body[0]->text);
        self::assertSame('Grandchild', $canonical->blocks[0]->listItems[0]->children[0]->children[0]->body[0]->text);
    }

    public function test_transform_maps_lowercase_legacy_image_block(): void
    {
        $document = [
            'blocks' => [[
                'id' => 'img-1',
                'type' => 'image',
                'data' => [
                    'file' => [
                        'url' => '/file/d0603a.png',
                    ],
                    'caption' => '',
                    'stretched' => false,
                ],
                'tunes' => [],
            ]],
        ];

        $canonical = $this->transformer->transform($document);

        self::assertCount(1, $canonical->blocks);
        self::assertSame('image', $canonical->blocks[0]->kind);
        self::assertSame('/file/d0603a.png', $canonical->blocks[0]->attrs['url']);
    }

    public function test_transform_maps_lowercase_legacy_imagerow_block(): void
    {
        $document = [
            'blocks' => [[
                'id' => 'gallery-1',
                'type' => 'imagerow',
                'data' => [
                    'images' => [[
                        'url' => 'https://example.com/file/c96faa.jpg',
                    ]],
                    'imagesPerRow' => 5,
                ],
                'tunes' => [],
            ]],
        ];

        $canonical = $this->transformer->transform($document);

        self::assertCount(1, $canonical->blocks);
        self::assertSame('image_gallery', $canonical->blocks[0]->kind);
        self::assertSame('https://example.com/file/c96faa.jpg', $canonical->blocks[0]->galleryImages[0]->url);
    }
}
