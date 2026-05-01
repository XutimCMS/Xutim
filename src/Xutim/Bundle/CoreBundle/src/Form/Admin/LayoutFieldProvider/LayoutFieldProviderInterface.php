<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Form\Admin\LayoutFieldProvider;

use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Xutim\CoreBundle\Config\Layout\Block\Option\BlockItemOption;

/**
 * Builds a single form field inside a `LayoutFormType` for a given
 * `BlockItemOption` type. Unlike `BlockItemProviderInterface`, field
 * names are provided by the caller (the `LayoutDefinition`), so the
 * same option type can be used for multiple fields in one form.
 */
interface LayoutFieldProviderInterface
{
    /**
     * The option class this provider knows how to build a field for.
     *
     * @return class-string<BlockItemOption>
     */
    public function getOptionClass(): string;

    /**
     * Add a single field named `$fieldName` to the builder.
     *
     * Accepts either a `FormBuilderInterface` (during initial form
     * construction) or a `FormInterface` (inside a `PRE_SET_DATA` /
     * `PRE_SUBMIT` event listener where fields are rebuilt dynamically).
     *
     * @param FormBuilderInterface<mixed>|FormInterface<mixed> $builder
     */
    public function buildField(
        FormBuilderInterface|FormInterface $builder,
        string $fieldName,
        BlockItemOption $option
    ): void;

    /**
     * Convert a raw persisted value (from editor.js `data.values`)
     * into the shape the Symfony field expects as initial data.
     */
    public function denormalizeForForm(mixed $storedValue): mixed;

    /**
     * Convert the form's submitted value into a JSON-serializable
     * shape suitable for persistence in editor.js `data.values`.
     */
    public function normalizeForStorage(mixed $formValue): mixed;
}
