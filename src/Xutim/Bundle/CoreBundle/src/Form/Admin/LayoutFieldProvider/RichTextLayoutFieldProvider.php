<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Form\Admin\LayoutFieldProvider;

use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Xutim\CoreBundle\Config\Layout\Block\Option\BlockItemOption;
use Xutim\CoreBundle\Config\Layout\Block\Option\RichTextBlockItemOption;

/**
 * Form field for RichTextBlockItemOption. The field is always hidden:
 * the real editing happens inline in the iframe preview. The form-level
 * round-trip (for modal Save) JSON-encodes the array into the hidden
 * input's value and decodes on submit, so the stored shape stays a
 * plain array.
 */
final readonly class RichTextLayoutFieldProvider implements LayoutFieldProviderInterface
{
    public function getOptionClass(): string
    {
        return RichTextBlockItemOption::class;
    }

    public function buildField(
        FormBuilderInterface|FormInterface $builder,
        string $fieldName,
        BlockItemOption $option
    ): void {
        $builder->add($fieldName, HiddenType::class, [
            'required' => false,
        ]);
    }

    public function denormalizeForForm(mixed $storedValue): mixed
    {
        if (!is_array($storedValue)) {
            return '';
        }

        return json_encode($storedValue, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }

    public function normalizeForStorage(mixed $formValue): mixed
    {
        if (!is_string($formValue) || $formValue === '') {
            return [];
        }

        $decoded = json_decode($formValue, true);
        if (!is_array($decoded)) {
            return [];
        }

        return $this->sanitize($decoded);
    }

    /**
     * Strip unknown marks and ensure the shape matches the TipTap inline
     * schema we support (text spans + bold/italic marks only). Any
     * non-text nodes or unknown marks are dropped silently.
     *
     * @param array<mixed> $nodes
     * @return list<array{type: string, text: string, marks?: list<array{type: string}>}>
     */
    private function sanitize(array $nodes): array
    {
        $allowedMarks = ['bold', 'italic'];
        $result = [];

        foreach ($nodes as $node) {
            if (!is_array($node)) {
                continue;
            }
            if (($node['type'] ?? null) !== 'text') {
                continue;
            }
            if (!is_string($node['text'] ?? null) || $node['text'] === '') {
                continue;
            }

            $marks = [];
            if (isset($node['marks']) && is_array($node['marks'])) {
                foreach ($node['marks'] as $mark) {
                    if (is_array($mark) && in_array($mark['type'] ?? null, $allowedMarks, true)) {
                        $marks[] = ['type' => $mark['type']];
                    }
                }
            }

            $span = ['type' => 'text', 'text' => $node['text']];
            if ($marks !== []) {
                $span['marks'] = $marks;
            }

            $result[] = $span;
        }

        return $result;
    }
}
