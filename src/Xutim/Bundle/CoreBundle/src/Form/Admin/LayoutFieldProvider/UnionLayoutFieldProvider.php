<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Form\Admin\LayoutFieldProvider;

use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Xutim\CoreBundle\Config\Layout\Block\Option\BlockItemOption;
use Xutim\CoreBundle\Config\Layout\Block\Option\BlockItemOptionUnion;

/**
 * Standalone union field: two sub-fields (`type` + `value`). Stored as
 * `['type' => 'page', 'value' => '<uuid>']`. Type is a select listing
 * the union's member option short-names; value is a free string that
 * the client-side tool swaps with the right picker on type change.
 */
final readonly class UnionLayoutFieldProvider implements LayoutFieldProviderInterface
{
    public function getOptionClass(): string
    {
        return BlockItemOptionUnion::class;
    }

    public function buildField(
        FormBuilderInterface|FormInterface $builder,
        string $fieldName,
        BlockItemOption $option
    ): void {
        if (!$option instanceof BlockItemOptionUnion) {
            throw new \LogicException('UnionLayoutFieldProvider requires BlockItemOptionUnion');
        }

        if (!$builder instanceof FormBuilderInterface) {
            // Union inside a union (nested) is not a practical case; the
            // event-listener path in LayoutCollectionEntryType only invokes
            // providers for scalar members (Page, Article, Text, …).
            throw new \LogicException('UnionLayoutFieldProvider can only be used at form build time (FormBuilderInterface), not inside event listeners.');
        }

        $members = $option->getDecomposedOptions();
        $choices = [];
        foreach ($members as $member) {
            $shortName = $this->shortName($member::class);
            $choices[$shortName] = $shortName;
        }

        $sub = $builder->create($fieldName, FormType::class, [
            'label' => $this->humanize($fieldName),
            'required' => false,
            'block_prefix' => 'xutim_layout_union',
            'by_reference' => false,
        ]);
        $sub->add('type', ChoiceType::class, [
            'choices' => $choices,
            'required' => true,
            'label' => false,
        ]);
        $sub->add('value', TextType::class, [
            'required' => false,
            'label' => false,
        ]);

        $builder->add($sub);
    }

    public function denormalizeForForm(mixed $storedValue): mixed
    {
        if (!is_array($storedValue)) {
            return null;
        }

        return [
            'type' => is_string($storedValue['type'] ?? null) ? $storedValue['type'] : null,
            'value' => is_string($storedValue['value'] ?? null) ? $storedValue['value'] : null,
        ];
    }

    public function normalizeForStorage(mixed $formValue): mixed
    {
        if (!is_array($formValue)) {
            return null;
        }

        $type = is_string($formValue['type'] ?? null) ? $formValue['type'] : null;
        $value = is_string($formValue['value'] ?? null) ? $formValue['value'] : null;

        if ($type === null || $value === null || $value === '') {
            return null;
        }

        return ['type' => $type, 'value' => $value];
    }

    private function shortName(string $fqcn): string
    {
        $pos = strrpos($fqcn, '\\');

        return $pos === false ? $fqcn : substr($fqcn, $pos + 1);
    }

    private function humanize(string $fieldName): string
    {
        return ucfirst(str_replace('_', ' ', $fieldName));
    }
}
