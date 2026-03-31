<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Tests\Unit\Content;

use PHPUnit\Framework\TestCase;
use Xutim\CoreBundle\Content\Adapter\CanonicalEditorJsAdapter;
use Xutim\CoreBundle\Content\CanonicalContentExtractor;
use Xutim\CoreBundle\Content\Diff\CanonicalContentDiffRenderer;
use Xutim\CoreBundle\Content\Diff\InlineDiffRenderer;
use Xutim\CoreBundle\Content\Transform\EditorJsToCanonicalDocumentTransformer;
use Xutim\CoreBundle\Content\Transform\InlineHtmlParser;

final class CanonicalContentDiffRendererTest extends TestCase
{
    private CanonicalEditorJsAdapter $adapter;
    private EditorJsToCanonicalDocumentTransformer $transformer;
    private CanonicalContentDiffRenderer $renderer;

    protected function setUp(): void
    {
        $this->adapter = new CanonicalEditorJsAdapter();
        $extractor = new CanonicalContentExtractor($this->adapter);

        $this->transformer = new EditorJsToCanonicalDocumentTransformer(new InlineHtmlParser());
        $this->renderer = new CanonicalContentDiffRenderer(
            $extractor,
            new InlineDiffRenderer(),
        );
    }

    public function test_removed_block_stays_in_context(): void
    {
        $old = $this->transformer->transform([
            'blocks' => [
                ['id' => 'a', 'type' => 'paragraph', 'data' => ['text' => 'Alpha'], 'tunes' => []],
                ['id' => 'b', 'type' => 'paragraph', 'data' => ['text' => 'Beta'], 'tunes' => []],
                ['id' => 'c', 'type' => 'paragraph', 'data' => ['text' => 'Gamma'], 'tunes' => []],
            ],
        ]);
        $new = $this->transformer->transform([
            'blocks' => [
                ['id' => 'a', 'type' => 'paragraph', 'data' => ['text' => 'Alpha'], 'tunes' => []],
                ['id' => 'c', 'type' => 'paragraph', 'data' => ['text' => 'Gamma'], 'tunes' => []],
            ],
        ]);

        $rows = $this->renderer->diffDocuments($old, $new);

        self::assertSame('a', $rows[0]['render_key']);
        self::assertSame('removed', $rows[1]['op']);
        self::assertSame('b', $rows[1]['render_key']);
        self::assertSame('c', $rows[2]['render_key']);
    }

    public function test_inserted_block_stays_in_middle_context(): void
    {
        $old = $this->transformer->transform([
            'blocks' => [
                ['id' => 'a', 'type' => 'paragraph', 'data' => ['text' => 'Alpha'], 'tunes' => []],
                ['id' => 'c', 'type' => 'paragraph', 'data' => ['text' => 'Gamma'], 'tunes' => []],
            ],
        ]);
        $new = $this->transformer->transform([
            'blocks' => [
                ['id' => 'a', 'type' => 'paragraph', 'data' => ['text' => 'Alpha'], 'tunes' => []],
                ['id' => 'b', 'type' => 'paragraph', 'data' => ['text' => 'Beta'], 'tunes' => []],
                ['id' => 'c', 'type' => 'paragraph', 'data' => ['text' => 'Gamma'], 'tunes' => []],
            ],
        ]);

        $rows = $this->renderer->diffDocuments($old, $new);

        self::assertSame('a', $rows[0]['render_key']);
        self::assertSame('added', $rows[1]['op']);
        self::assertSame('b', $rows[1]['render_key']);
        self::assertSame('c', $rows[2]['render_key']);
    }

    public function test_multiple_removals_do_not_sink_to_bottom(): void
    {
        $old = $this->transformer->transform([
            'blocks' => [
                ['id' => 'a', 'type' => 'paragraph', 'data' => ['text' => 'A'], 'tunes' => []],
                ['id' => 'b', 'type' => 'paragraph', 'data' => ['text' => 'B'], 'tunes' => []],
                ['id' => 'c', 'type' => 'paragraph', 'data' => ['text' => 'C'], 'tunes' => []],
                ['id' => 'd', 'type' => 'paragraph', 'data' => ['text' => 'D'], 'tunes' => []],
            ],
        ]);
        $new = $this->transformer->transform([
            'blocks' => [
                ['id' => 'a', 'type' => 'paragraph', 'data' => ['text' => 'A'], 'tunes' => []],
                ['id' => 'c', 'type' => 'paragraph', 'data' => ['text' => 'C'], 'tunes' => []],
            ],
        ]);

        $rows = $this->renderer->diffDocuments($old, $new);

        self::assertSame('removed', $rows[1]['op']);
        self::assertSame('b', $rows[1]['render_key']);
        self::assertSame('c', $rows[2]['render_key']);
        self::assertSame('removed', $rows[3]['op']);
        self::assertSame('d', $rows[3]['render_key']);
    }

    public function test_quote_caption_change_is_diffed_separately(): void
    {
        $old = $this->transformer->transform([
            'blocks' => [[
                'id' => 'q1',
                'type' => 'quote',
                'data' => ['text' => 'Body', 'caption' => 'Old caption', 'alignment' => 'center'],
                'tunes' => [],
            ]],
        ]);
        $new = $this->transformer->transform([
            'blocks' => [[
                'id' => 'q1',
                'type' => 'quote',
                'data' => ['text' => 'Body', 'caption' => 'New caption', 'alignment' => 'center'],
                'tunes' => [],
            ]],
        ]);

        $rows = $this->renderer->diffDocuments($old, $new);

        self::assertSame('modified', $rows[0]['op']);
        self::assertSame('same', $rows[0]['meta']['text']['status']);
        self::assertSame('changed', $rows[0]['meta']['caption']['status']);
        self::assertStringContainsString('<ins>', $rows[0]['meta']['caption']['html']);
    }

    public function test_list_change_produces_inline_diff_html(): void
    {
        $old = $this->transformer->transform([
            'blocks' => [[
                'id' => 'l1',
                'type' => 'list',
                'data' => [
                    'style' => 'unordered',
                    'meta' => ['start' => 1, 'counterType' => 'numeric'],
                    'items' => [['content' => 'One', 'meta' => '', 'items' => []]],
                ],
                'tunes' => [],
            ]],
        ]);
        $new = $this->transformer->transform([
            'blocks' => [[
                'id' => 'l1',
                'type' => 'list',
                'data' => [
                    'style' => 'unordered',
                    'meta' => ['start' => 1, 'counterType' => 'numeric'],
                    'items' => [['content' => 'One changed', 'meta' => '', 'items' => []]],
                ],
                'tunes' => [],
            ]],
        ]);

        $rows = $this->renderer->diffDocuments($old, $new);

        self::assertSame('modified_text', $rows[0]['op']);
        self::assertStringContainsString('<ins>', $rows[0]['body_html']);
    }

    public function test_nested_list_change_is_visible_in_diff(): void
    {
        $old = $this->transformer->transform([
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
                            'content' => 'Child old',
                            'meta' => '',
                            'items' => [],
                        ]],
                    ]],
                ],
                'tunes' => [],
            ]],
        ]);
        $new = $this->transformer->transform([
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
                            'content' => 'Child new',
                            'meta' => '',
                            'items' => [],
                        ]],
                    ]],
                ],
                'tunes' => [],
            ]],
        ]);

        $rows = $this->renderer->diffDocuments($old, $new);

        self::assertSame('modified_text', $rows[0]['op']);
        self::assertStringContainsString('Child', $rows[0]['body_html']);
        self::assertStringContainsString('<ins>', $rows[0]['body_html']);
    }

    public function test_formatting_only_change_wraps_old_and_new_markup(): void
    {
        $old = $this->transformer->transform([
            'blocks' => [[
                'id' => 'p1',
                'type' => 'paragraph',
                'data' => ['text' => '<strong><em>Hello</em></strong> world'],
                'tunes' => [],
            ]],
        ]);
        $new = $this->transformer->transform([
            'blocks' => [[
                'id' => 'p1',
                'type' => 'paragraph',
                'data' => ['text' => '<em>Hello</em> world'],
                'tunes' => [],
            ]],
        ]);

        $rows = $this->renderer->diffDocuments($old, $new);

        self::assertSame('modified_text', $rows[0]['op']);
        self::assertStringContainsString('<del><strong><em>Hello</em></strong> world</del>', $rows[0]['body_html']);
        self::assertStringContainsString('<ins><em>Hello</em> world</ins>', $rows[0]['body_html']);
    }

    public function test_foldable_attr_only_change_is_not_marked_as_text_change(): void
    {
        $old = $this->transformer->transform([
            'blocks' => [
                ['id' => 'f1', 'type' => 'foldableStart', 'data' => ['title' => 'Section', 'open' => false], 'tunes' => []],
                ['id' => 'fe1', 'type' => 'foldableEnd', 'data' => [], 'tunes' => []],
            ],
        ]);
        $new = $this->transformer->transform([
            'blocks' => [
                ['id' => 'f1', 'type' => 'foldableStart', 'data' => ['title' => 'Section', 'open' => true], 'tunes' => []],
                ['id' => 'fe1', 'type' => 'foldableEnd', 'data' => [], 'tunes' => []],
            ],
        ]);

        $rows = $this->renderer->diffDocuments($old, $new);

        self::assertSame('modified', $rows[0]['op']);
        self::assertStringContainsString('Section', $rows[0]['body_html']);
    }

    public function test_foldable_keeps_stable_legacy_ids_for_block_authors(): void
    {
        $document = $this->transformer->transform([
            'blocks' => [
                [
                    'id' => 'fold-1',
                    'type' => 'foldableStart',
                    'data' => ['title' => 'Section', 'open' => true],
                    'tunes' => [],
                ],
                [
                    'id' => 'p1',
                    'type' => 'paragraph',
                    'data' => ['text' => 'Inside'],
                    'tunes' => [],
                ],
                [
                    'id' => 'fold-end-1',
                    'type' => 'foldableEnd',
                    'data' => [],
                    'tunes' => [],
                ],
            ],
        ]);

        $legacyBlocks = $this->adapter->toEditorJsBlocks($document->blocks);

        self::assertSame('fold-1', $legacyBlocks[0]['id']);
        self::assertSame('p1', $legacyBlocks[1]['id']);
        self::assertSame('fold-1_end', $legacyBlocks[2]['id']);
    }
}
