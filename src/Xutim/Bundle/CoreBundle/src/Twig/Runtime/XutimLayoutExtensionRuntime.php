<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Twig\Runtime;

use Psr\Log\LoggerInterface;
use Twig\Environment;
use Twig\Extension\RuntimeExtensionInterface;
use Xutim\CoreBundle\Config\Layout\Block\Option\BlockItemOption;
use Xutim\CoreBundle\Config\Layout\Block\Option\InlineEditableOption;
use Xutim\CoreBundle\Config\Layout\Block\Option\RichTextBlockItemOption;
use Xutim\CoreBundle\Config\Layout\Block\Option\TextareaBlockItemOption;
use Xutim\CoreBundle\Config\Layout\Definition\LayoutDefinition;
use Xutim\CoreBundle\Config\Layout\Definition\LayoutDefinitionRegistry;
use Xutim\CoreBundle\Service\AdminEditUrl\AdminEditUrlResolver;
use Xutim\CoreBundle\Service\XutimLayoutValueResolver;

class XutimLayoutExtensionRuntime implements RuntimeExtensionInterface
{
    public function __construct(
        private readonly LayoutDefinitionRegistry $registry,
        private readonly XutimLayoutValueResolver $resolver,
        private readonly Environment $twig,
        private readonly LoggerInterface $logger,
        private readonly AdminEditUrlResolver $adminEditUrlResolver,
    ) {
    }

    /**
     * Returns all registered layout definitions in a JS-friendly shape
     * for the editor.js xutimLayout tool picker.
     *
     * @return list<array{code: string, name: string, description: string, category: string, previewImage: string, fields: list<array{name: string, translatable: bool, inlineEditable: bool, type: string}>}>
     */
    public function fetchLayouts(): array
    {
        $result = [];

        foreach ($this->registry->all() as $definition) {
            $fields = [];
            foreach ($definition->getFields() as $fieldName => $option) {
                $fields[] = [
                    'name' => $fieldName,
                    'translatable' => $option->isTranslatable(),
                    'inlineEditable' => $this->isInlineEditable($option),
                    'type' => $option::class,
                ];
            }

            $result[] = [
                'code' => $definition->getCode(),
                'name' => $definition->getName(),
                'description' => $definition->getDescription(),
                'category' => $definition->getCategory(),
                'previewImage' => $definition->getPreviewImage(),
                'fields' => $fields,
            ];
        }

        return $result;
    }

    /**
     * Render a xutimLayout editor.js fragment. Accepts the block's
     * `data` payload (`{layoutCode, values}`) as an associative array.
     * Unknown codes render as an empty string. Template failures render
     * as an empty string in production and as a visible red error block
     * in edit mode (so authors actually see the breakage).
     *
     * When `$editable` is true, the template context gets `editable=true`
     * so `xutim_editable()` helpers emit inline-edit markers instead of
     * plain text. Only used by the admin iframe preview.
     *
     * Twig component scope footgun for downstream layout templates.
     * Twig components leak their own prop defaults into the slot scope
     * (e.g. `<twig:Public:Container>` exposes `tag="div"`). Any
     * `{% set tag = values.tag %}` inside that container then gets
     * shadowed mid-template, and `tag.foo()` crashes with "Impossible
     * to invoke a method on a string variable". Use layout-specific
     * variable names — `letterTag`, `cardImage`, `pickedColor` — never
     * `tag`, `class`, `image`, `color`.
     *
     * @param array<string, mixed> $data
     */
    public function render(array $data, bool $editable = false): string
    {
        $layoutCode = $data['layoutCode'] ?? null;
        if (!is_string($layoutCode) || $layoutCode === '') {
            return '';
        }

        $definition = $this->registry->getByCode($layoutCode);
        if ($definition === null) {
            $this->logger->warning('Unknown xutim layout code in rendered content', [
                'code' => $layoutCode,
            ]);

            return '';
        }

        /** @var array<string, mixed> $rawValues */
        $rawValues = is_array($data['values'] ?? null) ? $data['values'] : [];

        try {
            $values = $this->resolver->resolve($definition, $rawValues);

            return $this->twig->render($definition->getTemplate(), [
                'layout' => $definition,
                'values' => $values,
                'editable' => $editable,
            ]);
        } catch (\Throwable $e) {
            $this->logger->warning('Failed to render xutim layout', [
                'code' => $layoutCode,
                'template' => $definition->getTemplate(),
                'error' => $e->getMessage(),
            ]);

            // In edit mode, surface the failure so content managers see
            // something instead of a blank iframe. In production keep
            // returning '' — a half-broken layout shouldn't leak stack
            // traces to public visitors.
            if ($editable) {
                return $this->renderEditableError($definition, $e);
            }

            return '';
        }
    }

    private function renderEditableError(LayoutDefinition $definition, \Throwable $e): string
    {
        return sprintf(
            '<div style="border:2px dashed #dc2626;background:#fee2e2;color:#7f1d1d;padding:16px;'
            . 'border-radius:8px;font-family:ui-sans-serif,system-ui,sans-serif;font-size:14px;'
            . 'line-height:1.5">'
            . '<div style="font-weight:600;margin-bottom:6px">xutim layout render failed</div>'
            . '<div style="font-size:12px;opacity:0.85;margin-bottom:8px">'
            . '<code>%s</code> — <code>%s</code>'
            . '</div>'
            . '<pre style="white-space:pre-wrap;word-break:break-word;font-size:12px;'
            . 'background:rgba(0,0,0,0.05);padding:8px;border-radius:4px;margin:0">%s</pre>'
            . '</div>',
            htmlspecialchars($definition->getCode(), ENT_QUOTES, 'UTF-8'),
            htmlspecialchars($definition->getTemplate(), ENT_QUOTES, 'UTF-8'),
            htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8'),
        );
    }

    /**
     * Emits a translatable-text marker used by the admin iframe preview
     * for inline editing. Outside edit mode, returns the plain value so
     * production rendering is unaffected.
     *
     * Multiline is auto-detected from the layout's field definition (any
     * TextareaBlockItemOption-backed field). Rich mode (bold + italic) is
     * auto-detected from RichTextBlockItemOption fields — the stored
     * value is a TipTap-shaped array of text spans, rendered as HTML by
     * `richTextHtml()`.
     *
     * MUST be used as slot content, not as a Twig component prop.
     * Component props auto-escape HTML, mangling the `<span data-xutim-editable>`
     * marker — the field will look fine but contenteditable silently breaks.
     *
     *     <twig:Public:Header>{{ xutim_editable('title', values.title) }}</twig:Public:Header>
     *
     * @param array<string, mixed> $context
     */
    public function editable(array $context, string $field, mixed $value): string
    {
        $layout = $context['layout'] ?? null;
        $fieldOption = $layout instanceof LayoutDefinition ? ($layout->getFields()[$field] ?? null) : null;
        $rich = $fieldOption instanceof RichTextBlockItemOption;

        // Resolve the rendered HTML/text for the current value, used both
        // outside edit mode (as-is) and inside edit mode (as the initial
        // contenteditable content).
        if ($rich) {
            $rendered = is_array($value) ? $this->richTextHtml($value) : '';
        } else {
            $plain = is_scalar($value) || $value === null ? (string) $value : '';
            $rendered = htmlspecialchars($plain, ENT_QUOTES, 'UTF-8');
        }

        if (($context['editable'] ?? false) !== true) {
            return $rendered;
        }

        $multiline = $fieldOption instanceof TextareaBlockItemOption;

        if ($fieldOption !== null && !$this->isInlineEditable($fieldOption)) {
            return $rendered;
        }

        $placeholder = ucfirst(strtolower(preg_replace('/([a-z])([A-Z])/', '$1 $2', $field) ?? $field));

        return sprintf(
            '<span data-xutim-editable="%s" data-xutim-placeholder="%s"%s%s>%s</span>',
            htmlspecialchars($field, ENT_QUOTES, 'UTF-8'),
            htmlspecialchars($placeholder, ENT_QUOTES, 'UTF-8'),
            $multiline ? ' data-xutim-multiline' : '',
            $rich ? ' data-xutim-rich' : '',
            $rendered,
        );
    }

    /**
     * Whether a field option is edited inline in the preview iframe
     * (via the `xutim_editable` twig helper) rather than through the
     * admin form. Used to hide such fields from the modal form and to
     * render them as hidden inputs so their values still round-trip.
     */
    public function isInlineEditable(BlockItemOption $option): bool
    {
        return $option instanceof InlineEditableOption;
    }

    /**
     * Renders a TipTap-shaped inline rich-text array to HTML, wrapping
     * text spans in `<b>`/`<i>` tags based on their marks. Unknown marks
     * are ignored. Input is trusted-after-read: the RichText form
     * provider sanitizes on storage, so this function just emits.
     *
     * @param array<mixed> $nodes
     */
    public function richTextHtml(array $nodes): string
    {
        $html = '';
        foreach ($nodes as $node) {
            if (!is_array($node) || ($node['type'] ?? null) !== 'text') {
                continue;
            }
            $text = is_string($node['text'] ?? null) ? $node['text'] : '';
            if ($text === '') {
                continue;
            }
            $escaped = htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
            $marks = is_array($node['marks'] ?? null) ? $node['marks'] : [];
            $hasBold = false;
            $hasItalic = false;
            foreach ($marks as $mark) {
                if (!is_array($mark)) {
                    continue;
                }
                if (($mark['type'] ?? null) === 'bold') {
                    $hasBold = true;
                }
                if (($mark['type'] ?? null) === 'italic') {
                    $hasItalic = true;
                }
            }
            if ($hasBold) {
                $escaped = '<b>' . $escaped . '</b>';
            }
            if ($hasItalic) {
                $escaped = '<i>' . $escaped . '</i>';
            }
            $html .= $escaped;
        }
        return $html;
    }

    /**
     * Opening marker for a block rendered from a linked entity (page,
     * article, …). In edit mode wraps content with a dashed frame and a
     * badge pointing to the entity's own translation edit page so
     * translators know that text must be edited there. Outside edit
     * mode returns an empty string — production rendering is unaffected.
     *
     * Usage (paired with `xutim_referenced_close`):
     *   {{ xutim_referenced_open('Main page', trans.title, admin_path('admin_content_translation_edit', {id: trans.id})) }}
     *       ...entity-sourced rendering...
     *   {{ xutim_referenced_close() }}
     *
     * @param array<string, mixed> $context
     */
    public function referencedOpen(array $context, string $label, string $name, string $editUrl = ''): string
    {
        if (($context['editable'] ?? false) !== true) {
            return '';
        }

        $badge = sprintf(
            '<span>%s</span>: <span>%s</span>',
            htmlspecialchars($label, ENT_QUOTES, 'UTF-8'),
            htmlspecialchars($name === '' ? '(untitled)' : $name, ENT_QUOTES, 'UTF-8'),
        );

        if ($editUrl !== '') {
            $badge = sprintf(
                '<a class="xutim-ref-badge xutim-ref-badge-link" href="%s" target="_blank" rel="noopener">%s ↗</a>',
                htmlspecialchars($editUrl, ENT_QUOTES, 'UTF-8'),
                $badge,
            );
        } else {
            $badge = '<span class="xutim-ref-badge">' . $badge . '</span>';
        }

        return '<div class="xutim-ref-block">' . $badge;
    }

    /**
     * Closing marker for `xutim_referenced_open`. Returns an empty
     * string outside edit mode; closes the wrapping div otherwise.
     *
     * @param array<string, mixed> $context
     */
    public function referencedClose(array $context): string
    {
        if (($context['editable'] ?? false) !== true) {
            return '';
        }

        return '</div>';
    }

    /**
     * Opening marker for a block rendered automatically by the layout
     * (shared dynamic blocks, live feeds, scheduled content, …) that
     * cannot be translated from here. In edit mode wraps content with
     * a slate-gray frame and an info badge saying "nothing to translate";
     * outside edit mode returns an empty string.
     *
     * Usage (paired with `xutim_static_close`):
     *   {{ xutim_static_open('Audio player', 'live / yesterday recordings') }}
     *       ...auto-rendered content...
     *   {{ xutim_static_close() }}
     *
     * @param array<string, mixed> $context
     */
    public function staticOpen(array $context, string $label, string $description = ''): string
    {
        if (($context['editable'] ?? false) !== true) {
            return '';
        }

        $badgeInner = '<span>' . htmlspecialchars($label, ENT_QUOTES, 'UTF-8') . '</span>';
        if ($description !== '') {
            $badgeInner .= ': <span>' . htmlspecialchars($description, ENT_QUOTES, 'UTF-8') . '</span>';
        }

        return '<div class="xutim-static-block"><span class="xutim-static-badge">' . $badgeInner . '</span>';
    }

    /**
     * Closing marker for `xutim_static_open`. Returns an empty string
     * outside edit mode; closes the wrapping div otherwise.
     *
     * @param array<string, mixed> $context
     */
    public function staticClose(array $context): string
    {
        if (($context['editable'] ?? false) !== true) {
            return '';
        }

        return '</div>';
    }

    /**
     * Resolves an entity to its admin edit URL for a given locale, or
     * the empty string if no registered resolver handles the entity
     * type. Intended to fill the `editUrl` argument of
     * `xutim_referenced_open` without per-type branching in templates.
     */
    public function adminEditUrl(object $entity, string $locale): string
    {
        return $this->adminEditUrlResolver->resolve($entity, $locale);
    }
}
