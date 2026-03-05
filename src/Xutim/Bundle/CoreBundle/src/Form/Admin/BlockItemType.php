<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Form\Admin;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\DataMapperInterface;
use Symfony\Component\Form\Exception\UnexpectedTypeException;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Translation\TranslatableMessage;
use Traversable;
use Xutim\CoreBundle\Config\Layout\Block\BlockOptionCollection;
use Xutim\CoreBundle\Form\Admin\BlockItemProvider\BlockItemProviderRegistry;
use Xutim\CoreBundle\Form\Admin\Dto\BlockItemDto;

/**
 * @template-extends AbstractType<BlockItemDto>
 * @template-implements DataMapperInterface<BlockItemDto>
 */
class BlockItemType extends AbstractType implements DataMapperInterface
{
    public function __construct(
        private readonly BlockItemProviderRegistry $registry,
    ) {
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'block_options' => new BlockOptionCollection([]),
        ]);

        $resolver->setAllowedTypes('block_options', ['object']);
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        /** @var BlockOptionCollection $blockOptions */
        $blockOptions = $options['block_options'];

        foreach ($this->registry->all() as $optionClass => $provider) {
            if ($blockOptions->hasOption($optionClass)) {
                $provider->buildFormFields($builder); // @phpstan-ignore argument.type (invariant generic)
            }
        }

        $builder
            ->add('submit', SubmitType::class, [
                'label' => new TranslatableMessage('submit', [], 'admin'),
            ])
            ->setDataMapper($this)
        ;
    }

    public function mapDataToForms(mixed $viewData, Traversable $forms): void
    {
        if ($viewData === null) {
            return;
        }

        if (!$viewData instanceof BlockItemDto) {
            throw new UnexpectedTypeException($viewData, BlockItemDto::class);
        }

        /** @var array<string, \Symfony\Component\Form\FormInterface<mixed>> $forms */
        $forms = iterator_to_array($forms);

        foreach ($this->registry->all() as $provider) {
            $provider->mapDataToForms($viewData, $forms);
        }
    }

    public function mapFormsToData(Traversable $forms, mixed &$viewData): void
    {
        /** @var array<string, \Symfony\Component\Form\FormInterface<mixed>> $forms */
        $forms = iterator_to_array($forms);
        $dto = new BlockItemDto();

        foreach ($this->registry->all() as $provider) {
            $provider->mapFormsToData($forms, $dto);
        }

        $viewData = $dto;
    }
}
