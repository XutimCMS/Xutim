<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Service;

use Xutim\CoreBundle\Config\Layout\Definition\LayoutDefinitionRegistry;
use Xutim\CoreBundle\Content\Diff\CanonicalContentDiffRenderer;
use Xutim\CoreBundle\Content\Transform\EditorJsToCanonicalDocumentTransformer;

final class EditorJsDiffRenderer
{
    public function __construct(
        private readonly EditorJsToCanonicalDocumentTransformer $transformer,
        private readonly CanonicalContentDiffRenderer $canonicalDiffRenderer,
        private readonly LayoutDefinitionRegistry $layoutRegistry,
    ) {
    }

    public function diffTitle(?string $old, ?string $new): string
    {
        return $this->canonicalDiffRenderer->diffText($old ?? '', $new ?? '');
    }

    public function diffDescription(?string $old, ?string $new): string
    {
        return $this->canonicalDiffRenderer->diffText($old ?? '', $new ?? '');
    }

    /**
     * @param EditorBlock $old
     * @param EditorBlock $new
     *
     * @return list<array<string, mixed>>
     */
    public function diffContent(array $old, array $new): array
    {
        return $this->canonicalDiffRenderer->diffDocuments(
            $this->transformer->transform($old),
            $this->transformer->transform($new),
        );
    }

    /**
     * For reference-diff (cross-locale drift) — strip rows that only
     * differ on translatable xutimLayout fields, since translatable
     * content is expected to differ across locales. Leaves structural
     * changes (missing blocks, different layoutCode, non-translatable
     * ref field changes) intact so genuine drift still surfaces.
     *
     * @param list<array<string, mixed>> $rows
     * @return list<array<string, mixed>>
     */
    public function filterTranslatableNoise(array $rows): array
    {
        $filtered = [];

        foreach ($rows as $row) {
            if (($row['kind'] ?? null) !== 'xutim_layout' || ($row['op'] ?? null) === 'unchanged') {
                $filtered[] = $row;
                continue;
            }

            if (($row['layout_code_changed'] ?? false) === true) {
                $filtered[] = $row;
                continue;
            }

            $layoutCode = is_string($row['layout_code'] ?? null) ? $row['layout_code'] : '';
            $definition = $layoutCode === '' ? null : $this->layoutRegistry->getByCode($layoutCode);

            /** @var array<string, array<string, mixed>> $meta */
            $meta = is_array($row['meta'] ?? null) ? $row['meta'] : [];

            $nonTranslatableChanged = false;
            foreach ($meta as $fieldName => $fieldMeta) {
                if (($fieldMeta['status'] ?? 'same') !== 'changed') {
                    continue;
                }
                if ($this->isFieldTranslatable($definition, $fieldName, $fieldMeta) === false) {
                    $nonTranslatableChanged = true;
                    break;
                }
            }

            if ($nonTranslatableChanged) {
                $filtered[] = $row;
            }
        }

        return $filtered;
    }

    /**
     * @param array<string, mixed> $fieldMeta
     */
    private function isFieldTranslatable(
        ?\Xutim\CoreBundle\Config\Layout\Definition\LayoutDefinition $definition,
        string $fieldName,
        array $fieldMeta,
    ): bool {
        if ($definition !== null) {
            $option = $definition->getFields()[$fieldName] ?? null;
            if ($option !== null) {
                return $option->isTranslatable();
            }
        }

        return ($fieldMeta['translatable'] ?? false) === true;
    }
}
