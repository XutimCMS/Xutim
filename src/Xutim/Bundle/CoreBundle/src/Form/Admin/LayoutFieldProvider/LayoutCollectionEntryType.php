<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Form\Admin\LayoutFieldProvider;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Xutim\CoreBundle\Config\Layout\Block\Option\BlockItemOption;
use Xutim\CoreBundle\Config\Layout\Block\Option\BlockItemOptionUnion;

/**
 * Form type rendered once per item inside a collection field. The inner
 * option determines the field shape:
 *   - Union  → `type` (select) + `value` (dynamically built via the
 *              matching member's LayoutFieldProvider — PRE_SET_DATA
 *              picks based on current data, PRE_SUBMIT picks based on
 *              submitted type so validation uses the right field).
 *   - scalar → single `value` built via the matching LayoutFieldProvider
 *
 * Submitted data shape per entry:
 *   union:  ['type' => 'PageBlockItemOption', 'value' => '<uuid>']
 *   scalar: ['value' => '...']
 *
 * @template-extends AbstractType<array<string, mixed>>
 */
class LayoutCollectionEntryType extends AbstractType
{
    public function __construct(
        private readonly LayoutFieldProviderRegistry $registry,
    ) {
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setRequired('inner_option');
        $resolver->setAllowedTypes('inner_option', BlockItemOption::class);
        $resolver->setDefault('data_class', null);
        $resolver->setDefault('empty_data', fn () => []);
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        /** @var BlockItemOption $innerOption */
        $innerOption = $options['inner_option'];

        if ($innerOption instanceof BlockItemOptionUnion) {
            $this->buildUnionEntry($builder, $innerOption); // @phpstan-ignore argument.type (invariant generic)

            return;
        }

        $provider = $this->registry->getForOption($innerOption);
        $provider->buildField($builder, 'value', $innerOption); // @phpstan-ignore argument.type (invariant generic)
    }

    /**
     * @param FormBuilderInterface<mixed> $builder
     */
    private function buildUnionEntry(FormBuilderInterface $builder, BlockItemOptionUnion $union): void
    {
        $memberOptions = $union->getDecomposedOptions();

        /** @var array<string, BlockItemOption> $membersByShortName */
        $membersByShortName = [];
        foreach ($memberOptions as $member) {
            $membersByShortName[$this->shortName($member::class)] = $member;
        }

        $choices = [];
        $choiceAttr = [];
        foreach ($membersByShortName as $shortName => $option) {
            $label = $option->getName();
            $choices[$label] = $shortName;
            $choiceAttr[$label] = [
                'data-description' => $option->getDescription() ?? '',
            ];
        }

        $firstKey = array_key_first($membersByShortName);

        $builder->add('type', ChoiceType::class, [
            'choices' => $choices,
            'choice_attr' => $choiceAttr,
            'required' => true,
            'label' => false,
            'expanded' => true,
            'multiple' => false,
            'block_prefix' => 'xutim_layout_union_type',
        ]);

        // Initial render: pick value field based on the stored type,
        // then denormalize the stored `value` through the matching
        // provider so EntityType-backed fields get an entity, not a raw
        // UUID string (which would fail `choice_value: 'id'` lookups).
        $builder->addEventListener(
            FormEvents::PRE_SET_DATA,
            function (FormEvent $event) use ($membersByShortName, $firstKey): void {
                $data = $event->getData();
                $type = is_array($data) && isset($data['type']) && is_string($data['type']) ? $data['type'] : $firstKey;
                $form = $event->getForm();

                $memberOption = $this->addValueField($form, $membersByShortName, $type);

                if ($memberOption === null || !is_array($data)) {
                    return;
                }

                $provider = $this->registry->getForOption($memberOption);
                $data['value'] = $provider->denormalizeForForm($data['value'] ?? null);
                $event->setData($data);
            }
        );

        // Submission: rebuild value field based on submitted type so
        // the matching provider validates and normalizes the value.
        $builder->addEventListener(
            FormEvents::PRE_SUBMIT,
            function (FormEvent $event) use ($membersByShortName, $firstKey): void {
                $data = $event->getData();
                $type = is_array($data) && isset($data['type']) && is_string($data['type']) ? $data['type'] : $firstKey;
                $this->addValueField($event->getForm(), $membersByShortName, $type);
            }
        );

        // After all child data has been bound, normalize the `value`
        // through the matching provider so the collection receives a
        // JSON-serializable shape (UUID strings, not entities). Without
        // this, EntityType-backed fields would leak entity objects up
        // into the stored values blob.
        $builder->addEventListener(
            FormEvents::SUBMIT,
            function (FormEvent $event) use ($membersByShortName): void {
                $data = $event->getData();
                if (!is_array($data)) {
                    return;
                }

                $type = is_string($data['type'] ?? null) ? $data['type'] : null;
                if ($type === null) {
                    return;
                }

                $memberOption = $membersByShortName[$type] ?? null;
                if ($memberOption === null) {
                    return;
                }

                $provider = $this->registry->getForOption($memberOption);
                $data['value'] = $provider->normalizeForStorage($data['value'] ?? null);
                $event->setData($data);
            }
        );
    }

    /**
     * @param FormInterface<mixed>              $form
     * @param array<string, BlockItemOption>    $membersByShortName
     */
    private function addValueField(FormInterface $form, array $membersByShortName, ?string $type): ?BlockItemOption
    {
        $memberOption = $type !== null ? ($membersByShortName[$type] ?? null) : null;

        if ($memberOption === null) {
            $form->add('value', TextType::class, [
                'required' => false,
                'label' => false,
            ]);

            return null;
        }

        $provider = $this->registry->getForOption($memberOption);
        $provider->buildField($form, 'value', $memberOption);

        return $memberOption;
    }

    private function shortName(string $fqcn): string
    {
        $pos = strrpos($fqcn, '\\');

        return $pos === false ? $fqcn : substr($fqcn, $pos + 1);
    }
}
