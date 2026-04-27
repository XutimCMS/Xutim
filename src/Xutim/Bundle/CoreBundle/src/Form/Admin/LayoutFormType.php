<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Form\Admin;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\DataMapperInterface;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Traversable;
use Xutim\CoreBundle\Config\Layout\Definition\LayoutDefinition;
use Xutim\CoreBundle\Form\Admin\Dto\LayoutValuesDto;
use Xutim\CoreBundle\Form\Admin\LayoutFieldProvider\LayoutFieldProviderRegistry;

/**
 * Dynamic form type that materializes one field per declared field in a
 * `LayoutDefinition`, dispatching to the matching `LayoutFieldProvider`.
 *
 * @template-extends AbstractType<LayoutValuesDto>
 * @template-implements DataMapperInterface<LayoutValuesDto>
 */
class LayoutFormType extends AbstractType implements DataMapperInterface
{
    public function __construct(
        private readonly LayoutFieldProviderRegistry $registry,
    ) {
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setRequired('layout_definition');
        $resolver->setAllowedTypes('layout_definition', LayoutDefinition::class);

        $resolver->setDefaults([
            'data_class' => LayoutValuesDto::class,
            'empty_data' => static fn () => new LayoutValuesDto(),
        ]);
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        /** @var LayoutDefinition $definition */
        $definition = $options['layout_definition'];

        foreach ($definition->getFields() as $fieldName => $option) {
            $provider = $this->registry->getForOption($option);
            $provider->buildField($builder, $fieldName, $option); // @phpstan-ignore argument.type (invariant generic)
        }

        $builder->setDataMapper($this);
        $builder->setAttribute('layout_definition', $definition);

        // Catch orphaned entity references: when a field's submitted view data
        // is non-empty but the field's resolved data is null, an EntityType
        // lookup silently failed (deleted entity, tampered POST, race
        // condition, …). Without this check the layout would save a null
        // value with no feedback, and the picked image / page / tag would
        // disappear after Save.
        $builder->addEventListener(FormEvents::POST_SUBMIT, $this->detectOrphanedReferences(...));
    }

    public function detectOrphanedReferences(FormEvent $event): void
    {
        $form = $event->getForm();

        foreach ($form->all() as $name => $child) {
            if ($child->getData() !== null) {
                continue;
            }

            $viewData = $child->getViewData();
            if (is_string($viewData) && $viewData !== '') {
                $child->addError(new FormError(sprintf(
                    'Field "%s" referenced "%s" but no matching entity was found — it may have been deleted.',
                    (string) $name,
                    $viewData,
                )));
            }
        }
    }

    public function mapDataToForms(mixed $viewData, Traversable $forms): void
    {
        if ($viewData === null) {
            return;
        }

        /** @var array<string, FormInterface<mixed>> $forms */
        $forms = iterator_to_array($forms);

        $definition = $this->resolveDefinition($forms);
        if ($definition === null) {
            return;
        }

        foreach ($definition->getFields() as $fieldName => $option) {
            if (!isset($forms[$fieldName])) {
                continue;
            }
            $provider = $this->registry->getForOption($option);
            $stored = $viewData->values[$fieldName] ?? null;
            $forms[$fieldName]->setData($provider->denormalizeForForm($stored));
        }
    }

    public function mapFormsToData(Traversable $forms, mixed &$viewData): void
    {
        /** @var array<string, FormInterface<mixed>> $forms */
        $forms = iterator_to_array($forms);

        $definition = $this->resolveDefinition($forms);
        if ($definition === null) {
            $viewData = new LayoutValuesDto();

            return;
        }

        $values = [];
        foreach ($definition->getFields() as $fieldName => $option) {
            if (!isset($forms[$fieldName])) {
                continue;
            }
            $provider = $this->registry->getForOption($option);
            $values[$fieldName] = $provider->normalizeForStorage($forms[$fieldName]->getData());
        }

        $viewData = new LayoutValuesDto($values);
    }

    /**
     * @param array<string, FormInterface<mixed>> $forms
     */
    private function resolveDefinition(array $forms): ?LayoutDefinition
    {
        $first = reset($forms);
        if ($first === false) {
            return null;
        }

        $parent = $first->getParent();
        if ($parent === null) {
            return null;
        }

        $definition = $parent->getConfig()->getAttribute('layout_definition');

        return $definition instanceof LayoutDefinition ? $definition : null;
    }
}
