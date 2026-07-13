<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Tests\Unit\Content;

use PHPUnit\Framework\TestCase;
use Xutim\CoreBundle\Config\Layout\Block\Option\PageBlockItemOption;
use Xutim\CoreBundle\Config\Layout\Block\Option\TextBlockItemOption;
use Xutim\CoreBundle\Config\Section\SectionDefinition;
use Xutim\CoreBundle\Config\Section\SectionDefinitionRegistry;
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
            new SectionDefinitionRegistry([]),
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
                ['id' => 'f1', 'type' => 'xutimFoldableStart', 'data' => ['title' => 'Section', 'open' => false], 'tunes' => []],
                ['id' => 'fe1', 'type' => 'xutimFoldableEnd', 'data' => [], 'tunes' => []],
            ],
        ]);
        $new = $this->transformer->transform([
            'blocks' => [
                ['id' => 'f1', 'type' => 'xutimFoldableStart', 'data' => ['title' => 'Section', 'open' => true], 'tunes' => []],
                ['id' => 'fe1', 'type' => 'xutimFoldableEnd', 'data' => [], 'tunes' => []],
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
                    'type' => 'xutimFoldableStart',
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
                    'type' => 'xutimFoldableEnd',
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

    public function test_added_xutim_section_block_has_layout_row_shape(): void
    {
        $old = $this->transformer->transform(['blocks' => []]);
        $new = $this->transformer->transform([
            'blocks' => [
                [
                    'id' => 'l1',
                    'type' => 'xutimSection',
                    'data' => [
                        'sectionCode' => 'two_columns',
                        'values' => ['title' => 'Hello', 'pageId' => '42'],
                    ],
                    'tunes' => [],
                ],
            ],
        ]);

        $rows = $this->renderer->diffDocuments($old, $new);

        self::assertSame('added', $rows[0]['op']);
        self::assertSame('section', $rows[0]['kind']);
        self::assertSame('two_columns', $rows[0]['section_code']);
        self::assertFalse($rows[0]['section_code_changed']);
        self::assertContains('title', $rows[0]['fields']);
        self::assertContains('pageId', $rows[0]['fields']);
        self::assertArrayHasKey('title', $rows[0]['parts']);
        self::assertArrayHasKey('title', $rows[0]['meta']);
    }

    public function test_layout_string_ref_field_with_definition_is_exposed_as_ref(): void
    {
        $renderer = $this->rendererWithSectionDefinition();
        $old = $this->transformer->transform([
            'blocks' => [
                [
                    'id' => 'l1',
                    'type' => 'xutimSection',
                    'data' => [
                        'sectionCode' => 'two_columns',
                        'values' => ['title' => 'Hello', 'page1' => '784305be-175d-4015-bf70-ce60ef40d34b'],
                    ],
                    'tunes' => [],
                ],
            ],
        ]);
        $new = $this->transformer->transform([
            'blocks' => [
                [
                    'id' => 'l1',
                    'type' => 'xutimSection',
                    'data' => [
                        'sectionCode' => 'two_columns',
                        'values' => ['title' => 'Hello', 'page1' => '59603a51-c534-46ac-84c3-25d28484b483'],
                    ],
                    'tunes' => [],
                ],
            ],
        ]);

        $rows = $renderer->diffDocuments($old, $new);

        self::assertSame('ref', $rows[0]['parts']['page1']['kind']);
        self::assertSame('changed', $rows[0]['meta']['page1']['status']);
        self::assertSame('784305be-175d-4015-bf70-ce60ef40d34b', $rows[0]['meta']['page1']['old_raw']);
        self::assertSame('59603a51-c534-46ac-84c3-25d28484b483', $rows[0]['meta']['page1']['new_raw']);
        self::assertFalse($rows[0]['meta']['page1']['translatable']);
        self::assertSame('text', $rows[0]['parts']['title']['kind']);
        self::assertTrue($rows[0]['meta']['title']['translatable']);
        self::assertSame('modified', $rows[0]['op']);
    }

    public function test_removed_xutim_section_block_has_layout_row_shape(): void
    {
        $old = $this->transformer->transform([
            'blocks' => [
                [
                    'id' => 'l1',
                    'type' => 'xutimSection',
                    'data' => [
                        'sectionCode' => 'two_columns',
                        'values' => ['title' => 'Hello'],
                    ],
                    'tunes' => [],
                ],
            ],
        ]);
        $new = $this->transformer->transform(['blocks' => []]);

        $rows = $this->renderer->diffDocuments($old, $new);

        self::assertSame('removed', $rows[0]['op']);
        self::assertSame('section', $rows[0]['kind']);
        self::assertSame('two_columns', $rows[0]['section_code']);
        self::assertFalse($rows[0]['section_code_changed']);
        self::assertContains('title', $rows[0]['fields']);
    }

    private function rendererWithSectionDefinition(): CanonicalContentDiffRenderer
    {
        $definition = new class implements SectionDefinition {
            public function getCode(): string
            {
                return 'two_columns';
            }

            public function getName(): string
            {
                return 'Two columns';
            }

            public function getFields(): array
            {
                return [
                    'title' => new TextBlockItemOption(),
                    'page1' => new PageBlockItemOption(),
                ];
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

        return new CanonicalContentDiffRenderer(
            new CanonicalContentExtractor($this->adapter),
            new InlineDiffRenderer(),
            new SectionDefinitionRegistry([$definition]),
        );
    }
}
